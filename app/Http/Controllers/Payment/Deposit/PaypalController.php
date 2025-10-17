<?php

namespace App\Http\Controllers\Payment\Deposit;

use App\{
    Models\Deposit,
    Classes\GeniusMailer,
    Models\PaymentGateway
};

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Services\Paypal\PaypalClient;

use Redirect;
use Session;
use Throwable;

class PaypalController extends DepositBaseController
{
    private PaypalClient $paypalClient;
    private array $gatewayConfig = [];
    private array $supportedCurrencies = [];

    public function __construct()
    {
        parent::__construct();
        $gateway = PaymentGateway::whereKeyword('paypal')->firstOrFail();
        $this->gatewayConfig = $gateway->convertAutoData();
        $this->supportedCurrencies = json_decode($gateway->currency_id, true) ?? [];
        $this->paypalClient = new PaypalClient([
            'client_id' => $this->gatewayConfig['client_id'] ?? null,
            'secret' => $this->gatewayConfig['client_secret'] ?? null,
            'mode' => ($this->gatewayConfig['sandbox_check'] ?? 0) == 1 ? 'sandbox' : 'live',
        ]);
    }

    public function store(Request $request){

        $user = $this->user;

       $item_amount = $request->amount;
        $curr = $this->curr;

        if(!in_array($curr->id,$this->supportedCurrencies)){
            return redirect()->back()->with('unsuccess',__('Invalid Currency For Paypal Payment.'));
        }

        $item_name = "Deposit via Paypal Payment";
        $cancel_url = route('deposit.payment.cancle');
        $notify_url = route('deposit.paypal.notify');

        $dep['user_id'] = $user->id;
        $dep['currency'] = $this->curr->sign;
        $dep['currency_code'] = $this->curr->name;
        $dep['amount'] = $request->amount / $this->curr->value;
        $dep['currency_value'] = $this->curr->value;
        $dep['method'] = 'Paypal';

        try {
            $orderResponse = $this->paypalClient->createOrder(
                $item_amount,
                $curr->name,
                $notify_url,
                $cancel_url,
                [
                    'description' => $item_name,
                    'custom_id' => Str::random(4).time(),
                    'brand_name' => $this->gs->title,
                ]
            );
        } catch (Throwable $ex) {
            return redirect()->back()->with('unsuccess',$ex->getMessage());
        }

        $redirect_url = null;
        foreach ($orderResponse['links'] ?? [] as $link) {
            if (($link['rel'] ?? '') === 'approve') {
                $redirect_url = $link['href'] ?? null;
                break;
            }
        }

        if (!$redirect_url || empty($orderResponse['id'])) {
            return redirect()->back()->with('unsuccess',__('Unable to initiate PayPal checkout.'));
        }

        /** add payment ID to session **/
        Session::put('deposit',$dep);
        Session::put('paypal_payment_id', $orderResponse['id']);

        return Redirect::away($redirect_url);
        
    }

      public function notify(Request $request){

        $dep = Session::get('deposit');
        $success_url = route('deposit.payment.return');
        $cancel_url = route('deposit.payment.cancle');
        $token = $request->query('token', Session::get('paypal_payment_id'));

        if (empty($dep) || empty($token)) {
            return redirect($cancel_url);
        }

        try {
            $result = $this->paypalClient->captureOrder($token);
        } catch (Throwable $ex) {
            return redirect($cancel_url)->with('unsuccess', $ex->getMessage());
        }

        if (($result['status'] ?? '') === 'COMPLETED') {
            $resp = $result;

                $deposit = new Deposit;
                $deposit->user_id = $dep['user_id'];
                $deposit->currency = $dep['currency'];
                $deposit->currency_code = $dep['currency_code'];
                $deposit->amount = $dep['amount'];
                $deposit->currency_value = $dep['currency_value'];
                $deposit->method = $dep['method'];
                $deposit->txnid = data_get($resp, 'purchase_units.0.payments.captures.0.id');
                $deposit->status = 1;
                $deposit->save();

                $user = \App\Models\User::findOrFail($deposit->user_id);
                $user->balance = $user->balance + ($deposit->amount);
                $user->save();

                // store in transaction table
                if ($deposit->status == 1) {
                    $transaction = new \App\Models\Transaction;
                    $transaction->txn_number = Str::random(3).substr(time(), 6,8).Str::random(3);
                    $transaction->user_id = $deposit->user_id;
                    $transaction->amount = $deposit->amount;
                    $transaction->user_id = $deposit->user_id;
                    $transaction->currency_sign = $deposit->currency;
                    $transaction->currency_code = $deposit->currency_code;
                    $transaction->currency_value= $deposit->currency_value;
                    $transaction->method = $deposit->method;
                    $transaction->txnid = $deposit->txnid;
                    $transaction->details = 'Payment Deposit';
                    $transaction->type = 'plus';
                    $transaction->save();
                }
            
                $maildata = [
                    'to' => $user->email,
                    'type' => "wallet_deposit",
                    'cname' => $user->name,
                    'damount' => $deposit->amount,
                    'wbalance' => $user->balance,
                    'oamount' => "",
                    'aname' => "",
                    'aemail' => "",
                    'onumber' => "",
                ];

                $mailer = new GeniusMailer();
                $mailer->sendAutoMail($maildata);

            Session::forget('deposit');
            Session::forget('paypal_payment_id');
            return redirect($success_url);
        }
        else {
            return redirect($cancel_url);
        }

    }
}
