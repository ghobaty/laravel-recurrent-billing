<?php namespace Ghobaty\Billing\Http\Controllers;

use App\Model\User;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Subscription;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends \Laravel\Cashier\Http\Controllers\WebhookController
{

    /**
     * Handle customer subscription updated.
     *
     * @param array $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleCheckoutSessionCompleted(array $payload)
    {
        info(json_encode($payload));
        $customer = $payload['data']['object']['customer'];
        $userId = $payload['data']['object']['client_reference_id'];
        $subscription = $payload['data']['object']['subscription'];

        $user = User::findOrFail($userId);

        $user->subscriptions()->create([
            'name'          => $this->name,
            'stripe_id'     => $subscription->id,
            'stripe_plan'   => $this->plan,
            'quantity'      => $this->quantity,
            'trial_ends_at' => $trialEndsAt,
            'ends_at'       => null,
        ]);
//
//        if ($user) {
//            $data = $payload['data']['object'];
//
//            $user->subscriptions->filter(function (Subscription $subscription) use ($data) {
//                return $subscription->stripe_id === $data['id'];
//            })->each(function (Subscription $subscription) use ($data) {
//                // Quantity...
//                if (isset($data['quantity'])) {
//                    $subscription->quantity = $data['quantity'];
//                }
//
//                // Plan...
//                if (isset($data['plan']['id'])) {
//                    $subscription->stripe_plan = $data['plan']['id'];
//                }
//
//                // Trial ending date...
//                if (isset($data['trial_end'])) {
//                    $trial_ends = Carbon::createFromTimestamp($data['trial_end']);
//
//                    if (! $subscription->trial_ends_at || $subscription->trial_ends_at->ne($trial_ends)) {
//                        $subscription->trial_ends_at = $trial_ends;
//                    }
//                }
//
//                // Cancellation date...
//                if (isset($data['cancel_at_period_end']) && $data['cancel_at_period_end']) {
//                    $subscription->ends_at = $subscription->onTrial()
//                        ? $subscription->trial_ends_at
//                        : Carbon::createFromTimestamp($data['current_period_end']);
//                }
//
//                $subscription->save();
//            });
//        }
//
        return new Response('Webhook Handled', 200);
    }

}
