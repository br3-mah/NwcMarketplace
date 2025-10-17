<?php

namespace App\Http\Controllers\Payment\Subscription;

use App\{
    Models\User,
    Models\Subscription,
    Classes\GeniusMailer,
    Models\PaymentGateway,
    Models\UserSubscription
};

use Illuminate\{
    Http\Request,
    Support\Facades\Session
};

use App\Services\Paypal\PaypalClient;

use Redirect;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Throwable;

class PaypalController extends SubscriptionBaseController
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

        $this->validate($request, [
                'shop_name'   => 'unique:users',
            ],[ 
                'shop_name.unique' => __('This shop name has already been taken.')
            ]);

        $subs = Subscription::findOrFail($request->subs_id);
        $user = $this->user;

        $item_amount = $subs->price * $this->curr->value;
        $curr = $this->curr;

        if(!in_array($curr->id,$this->supportedCurrencies)){
            return redirect()->back()->with('unsuccess',__('Invalid Currency For Paypal Payment.'));
        }

        $sub['user_id'] = $user->id;
        $sub['subscription_id'] = $subs->id;
        $sub['title'] = $subs->title;
        $sub['currency_sign'] = $this->curr->sign;
        $sub['currency_code'] = $this->curr->name;
        $sub['currency_value'] = $this->curr->value;
        $sub['price'] = $subs->price * $this->curr->value;
        $sub['price'] = $sub['price'] / $this->curr->value;
        $sub['days'] = $subs->days;
        $sub['allowed_products'] = $subs->allowed_products;
        $sub['details'] = $subs->details;
        $sub['method'] = 'Paypal';     
    
        $order['item_name'] = $subs->title." Plan";
        $order['item_number'] = Str::random(4).time();
        $order['item_amount'] = $item_amount;
        $cancel_url = route('user.payment.cancle');
        $notify_url = route('user.paypal.notify');
    
        try {
            $orderResponse = $this->paypalClient->createOrder(
                $order['item_amount'],
                $curr->name,
                $notify_url,
                $cancel_url,
                [
                    'description' => $order['item_name'].' Via Paypal',
                    'custom_id' => $order['item_number'],
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
        Session::put('paypal_data',$sub);
        Session::put('paypal_payment_id', $orderResponse['id']);

        return Redirect::away($redirect_url);
     }

     public function notify(Request $request){

        $paypal_data = Session::get('paypal_data');
        $success_url = route('user.payment.return');
        $cancel_url = route('user.payment.cancle');
        $input = $request->all();
        $token = $request->query('token', Session::get('paypal_payment_id'));

        if (empty($paypal_data) || empty($token)) {
            return redirect($cancel_url);
        }

        try {
            $result = $this->paypalClient->captureOrder($token);
        } catch (Throwable $ex) {
            return redirect($cancel_url)->with('unsuccess', $ex->getMessage());
        }

        if (($result['status'] ?? '') === 'COMPLETED') {

            $order = new UserSubscription;
            $order->user_id = $paypal_data['user_id'];
            $order->subscription_id = $paypal_data['subscription_id'];
            $order->title = $paypal_data['title'];
            $order->currency_sign = $this->curr->sign;
            $order->currency_code = $this->curr->name;
            $order->currency_value = $this->curr->value;
            $order->price = $paypal_data['price'];
            $order->days = $paypal_data['days'];
            $order->allowed_products = $paypal_data['allowed_products'];
            $order->details = $paypal_data['details'];
            $order->method = $paypal_data['method'];
            $order->txnid = data_get($result, 'purchase_units.0.payments.captures.0.id');
            $order->status = 1;

            $user = User::findOrFail($order->user_id);
            $package = $user->subscribes()->where('status',1)->orderBy('id','desc')->first();
            $subs = Subscription::findOrFail($order->subscription_id);

            $today = Carbon::now()->format('Y-m-d');
            $user->is_vendor = 2;
            if(!empty($package))
            {
                if($package->subscription_id == $order->subscription_id)
                {
                    $newday = strtotime($today);
                    $lastday = strtotime($user->date);
                    $secs = $lastday-$newday;
                    $days = $secs / 86400;
                    $total = $days+$subs->days;
                    $input['date'] = date('Y-m-d', strtotime($today.' + '.$total.' days'));
                }
                else
                {
                    $input['date'] = date('Y-m-d', strtotime($today.' + '.$subs->days.' days'));
                }
            }
            else
            {
                        
                $input['date'] = date('Y-m-d', strtotime($today.' + '.$subs->days.' days'));

            }

                    $input['mail_sent'] = 1;
                    $user->update($input);
                    $order->save();

                        $maildata = [
                            'to' => $user->email,
                            'type' => "vendor_accept",
                            'cname' => $user->name,
                            'oamount' => "",
                            'aname' => "",
                            'aemail' => "",
                            'onumber' => "",
                        ];
                        $mailer = new GeniusMailer();
                        $mailer->sendAutoMail($maildata);

                    Session::forget('payment_id');
                    Session::forget('molly_data');
                    Session::forget('user_data');
                    Session::forget('order_data');
                    Session::forget('paypal_data');
                    Session::forget('paypal_payment_id');

                        return redirect($success_url);
                    }
                    else {
                        return redirect($cancel_url);
                    }

    }

}
