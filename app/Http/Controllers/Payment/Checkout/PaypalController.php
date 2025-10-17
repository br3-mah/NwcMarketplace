<?php

namespace App\Http\Controllers\Payment\Checkout;

use App\{
    Models\Cart,
    Models\Order,
    Classes\GeniusMailer,
    Models\PaymentGateway
};
use App\Models\Country;
use App\Models\Reward;
use App\Models\State;
use App\Services\Paypal\PaypalClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use OrderHelper;
use Illuminate\Support\Str;
use Throwable;


class PaypalController extends CheckoutBaseControlller
{
    private PaypalClient $paypalClient;
    private array $gatewayConfig = [];

    public function __construct()
    {
        parent::__construct();
        $gateway = PaymentGateway::whereKeyword('paypal')->firstOrFail();
        $this->gatewayConfig = $gateway->convertAutoData();
        $this->paypalClient = new PaypalClient([
            'client_id' => $this->gatewayConfig['client_id'] ?? null,
            'secret' => $this->gatewayConfig['client_secret'] ?? null,
            'mode' => ($this->gatewayConfig['sandbox_check'] ?? 0) == 1 ? 'sandbox' : 'live',
        ]);
    }

    public function store(Request $request)
    {

        $input = $request->all();
        $total = $request->total / $this->curr->value;
        $total = $total * $this->curr->value;
        OrderHelper::set_currency($this->curr->value); // For Converting Price

        $input['currency_sign'] = $this->curr->sign;
        $input['currency_name'] = $this->curr->value;

        if($request->pass_check) {
            $auth = OrderHelper::auth_check($input); // For Authentication Checking
            if(!$auth['auth_success']){
                return redirect()->back()->with('unsuccess',$auth['error_message']);
            }
        }

        if (!Session::has('cart')) {
            return redirect()->route('front.cart')->with('success',__("You don't have any product to checkout."));
        }

        $order['item_name'] = $this->gs->title." Order";
        $order['item_number'] = Str::random(4).time();
        $order['item_amount'] = $total;
        $cancel_url = route('front.payment.cancle');
        $notify_url = route('front.paypal.notify');

        try {
            $orderResponse = $this->paypalClient->createOrder(
                $order['item_amount'],
                $this->curr->name,
                $notify_url,
                $cancel_url,
                [
                    'description' => $order['item_name'] . ' Via Paypal',
                    'custom_id' => $order['item_number'],
                    'brand_name' => $this->gs->title,
                    'shipping_preference' => 'NO_SHIPPING',
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
        Session::put('input_data',$input);
        Session::put('order_data',$order);
        Session::put('order_payment_id', $orderResponse['id']);

        return \Redirect::away($redirect_url);

    }

    public function notify(Request $request)
    {
        $input = Session::get('input_data');
        $order_data = Session::get('order_data');
        $success_url = route('front.payment.return');
        $cancel_url = route('front.payment.cancle');
        $token = $request->query('token', Session::get('order_payment_id'));

        if (!$token || !$input || !$order_data) {
            return redirect($cancel_url);
        }

        try {
            $result = $this->paypalClient->captureOrder($token);
        } catch (Throwable $ex) {
            return redirect($cancel_url)->with('unsuccess', $ex->getMessage());
        }

        if (($result['status'] ?? '') === 'COMPLETED') {
            $resp = $result;

            $oldCart = Session::get('cart');
            $cart = new Cart($oldCart);
            OrderHelper::license_check($cart); // For License Checking
            $t_oldCart = Session::get('cart');
            $t_cart = new Cart($t_oldCart);
            $new_cart = [];
            $new_cart['totalQty'] = $t_cart->totalQty;
            $new_cart['totalPrice'] = $t_cart->totalPrice;
            $new_cart['items'] = $t_cart->items;
            $new_cart = json_encode($new_cart);
            $temp_affilate_users = OrderHelper::product_affilate_check($cart); // For Product Based Affilate Checking
            $affilate_users = $temp_affilate_users == null ? null : json_encode($temp_affilate_users);

            $order = new Order;
            $input['cart'] = $new_cart;
            $input['user_id'] = Auth::check() ? Auth::user()->id : NULL;
            $input['affilate_users'] = $affilate_users;
            $input['pay_amount'] = $order_data['item_amount'] / $this->curr->value;
            $input['order_number'] = $order_data['item_number'];
            $input['wallet_price'] = $input['wallet_price'] / $this->curr->value;
            $input['payment_status'] = "Completed";
            if($input['tax_type'] == 'state_tax'){
                $input['tax_location'] = State::findOrFail($input['tax'])->state;
            }else{
                $input['tax_location'] = Country::findOrFail($input['tax'])->country_name;
            }
            $input['tax'] = Session::get('current_tax');

            $input['txnid'] = data_get($resp, 'purchase_units.0.payments.captures.0.id');
            if($input['dp'] == 1){
                $input['status'] = 'completed';
            }
            if (Session::has('affilate')) {
                $totalAmount = $request->total ?? $order_data['item_amount'];
                $val = $totalAmount / $this->curr->value;
                $val = $val / 100;
                $sub = $val * $this->gs->affilate_charge;
                if($temp_affilate_users != null){
                    $t_sub = 0;
                    foreach($temp_affilate_users as $t_cost){
                        $t_sub += $t_cost['charge'];
                    }
                    $sub = $sub - $t_sub;
                }
                if($sub > 0){
                    $user = OrderHelper::affilate_check(Session::get('affilate'),$sub,$input['dp']); // For Affiliate Checking
                    $input['affilate_user'] = Session::get('affilate');
                    $input['affilate_charge'] = $sub;
                }

            }

            $order->fill($input)->save();
            $order->tracks()->create(['title' => 'Pending', 'text' => 'You have successfully placed your order.' ]);
            $order->notifications()->create();

            if($input['coupon_id'] != "") {
                OrderHelper::coupon_check($input['coupon_id']); // For Coupon Checking
            }

            OrderHelper::size_qty_check($cart); // For Size Quantiy Checking
            OrderHelper::stock_check($cart); // For Stock Checking
            OrderHelper::vendor_order_check($cart,$order); // For Vendor Order Checking

            Session::put('temporder',$order);
            Session::put('tempcart',$cart);
            Session::forget('cart');
            Session::forget('already');
            Session::forget('coupon');
            Session::forget('coupon_total');
            Session::forget('coupon_total1');
            Session::forget('coupon_percentage');

            if ($order->user_id != 0 && $order->wallet_price != 0) {
                OrderHelper::add_to_transaction($order,$order->wallet_price); // Store To Transactions
            }

            if(Auth::check()){
                if($this->gs->is_reward == 1){
                    $num = $order->pay_amount;
                    $rewards = Reward::get();
                    foreach ($rewards as $i) {
                        $smallest[$i->order_amount] = abs($i->order_amount - $num);
                    }

                    asort($smallest);
                    $final_reword = Reward::where('order_amount',key($smallest))->first();
                    Auth::user()->update(['reward' => (Auth::user()->reward + $final_reword->reward)]);
                }
            }

            //Sending Email To Buyer
            $data = [
                'to' => $order->customer_email,
                'type' => "new_order",
                'cname' => $order->customer_name,
                'oamount' => "",
                'aname' => "",
                'aemail' => "",
                'wtitle' => "",
                'onumber' => $order->order_number,
            ];
            $mailer = new GeniusMailer();
            $mailer->sendAutoOrderMail($data,$order->id);

            //Sending Email To Admin
            $data = [
                'to' => $this->ps->contact_email,
                'subject' => "New Order Recieved!!",
                'body' => "Hello Admin!<br>Your store has received a new order.<br>Order Number is ".$order->order_number.".Please login to your panel to check. <br>Thank you.",
            ];
            $mailer = new GeniusMailer();
            $mailer->sendCustomMail($data);

            return redirect($success_url);

        }
        return redirect($cancel_url);
    }
}
