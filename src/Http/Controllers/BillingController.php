<?php namespace Ghobaty\Billing\Http\Controllers;

use Ghobaty\Billing\Plan;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BillingController extends Controller
{
    /**
     * TODO show next billing date.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $subscription = optional($this->billable()->subscription());

        $id = $subscription->stripe_plan;

        return view('billing.index', [
            'current'      => $id ? Plan::byId($id) : Plan::free()->first(),
            'subscription' => $subscription,
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showSubscribeForm()
    {
        $id = optional($this->billable()->subscription())->stripe_plan;

        return view('billing.subscribe', [
            'plans'    => Plan::active(),
            'currency' => config('billing.currency'),
            'current'  => $id ? Plan::byId($id) : Plan::free()->first(),
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @throws \Throwable
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function subscribe(Request $request)
    {
        /** @var \Laravel\Cashier\Billable $billable */
        $billable = $this->billable();

        if ($billable->subscribed()) {
            throw new BadRequestHttpException(__('You are already on a paid plan.'));
        }

        $plan = $this->plan();

        $data = $request->validate(['token' => 'required|string']);

        try {
            $billable->newSubscription('default', $plan->id)->create($data['token'], [
                'email' => $billable->email,
            ]);
        } catch (\Stripe\Error\InvalidRequest $e) {
            Log::error($e);
            throw new BadRequestHttpException($e->getMessage());
        }

        session()->flash('status', "You have successfully upgraded to {$plan->description} plan. Enjoy!");

        return redirect()->route('billing.subscription');
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function changePlan()
    {
        /** @var \Laravel\Cashier\Billable $billable */
        $billable = $this->billable();

        if (! $billable->subscribed() || $billable->subscription()->cancelled()) {
            throw new BadRequestHttpException('You are not on an active paid plan.');
        }

        $plan = $this->plan();

        try {
            $billable->subscription()->swap($plan->id);
        } catch (\Stripe\Error\InvalidRequest $e) {
            Log::error($e);
            throw new BadRequestHttpException($e->getMessage());
        }

        session()->flash('status', "Your subscription plan has been changed to {$plan->description}. Enjoy!");

        return redirect()->route('billing.subscription');
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy()
    {
        /** @var \Laravel\Cashier\Billable $billable */
        $billable = $this->billable();

        if (! $billable->subscribed() || $billable->subscription()->cancelled()) {
            throw new BadRequestHttpException('You are not on an active paid plan.');
        }

        try {
            $billable->subscription()->cancel();
        } catch (\Stripe\Error\InvalidRequest $e) {
            Log::error($e);
            throw new BadRequestHttpException($e->getMessage());
        }

        session()->flash('status', "Your subscription has been cancelled. You can still enjoy your plan privileges until the end of your billing cycle.");

        return redirect()->route('billing.subscription');
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function restore()
    {
        $billable = $this->billable();

        if (! $billable->subscribed() || ! $billable->subscription()->cancelled()) {
            throw new BadRequestHttpException('Nothing to restore.');
        }

        try {
            $billable->subscription()->resume();
        } catch (\Stripe\Error\InvalidRequest $e) {
            Log::error($e);
            throw new BadRequestHttpException($e->getMessage());
        }

        session()->flash('status', "Your subscription has been resumed. Enjoy!");

        return redirect()->route('billing.subscription');
    }

    /**
     * TODO move to another bot independent class
     *
     * @param Request $request
     * @param \App\Http\Controllers\
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function updateCard(Request $request)
    {
        /** @var \Laravel\Cashier\Billable $billable */
        $billable = $this->billable();

        if (! $billable->subscribed()) {
            throw new BadRequestHttpException('You are not subscribed to any billed plan.');
        }

        $data = $request->validate(['token' => 'required|string']);

        try {
            $billable->updateCard($data['token']);
        } catch (\Stripe\Error\InvalidRequest $e) {
            Log::error($e);
            throw new BadRequestHttpException($e->getMessage());
        }

        session()->flash('status', "Your card information has been successfully updated.");

        return redirect()->route('billing.subscription');
    }

    /**
     * TODO locally-stored invoices
     * TODO upcoming Invoice
     *
     * @return \Illuminate\Support\Collection
     */
    public function invoices()
    {
        $invoices = $this->billable()->stripe_id ? $this->billable()->invoices() : [];

        return view('billing.invoices', compact('invoices'));
    }

    /**
     * TODO customize
     *
     * @param string $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function invoice(string $id)
    {
        return $this->billable()->downloadInvoice($id, [
            'vendor'  => config('app.name'),
            'product' => 'Subscription',
        ]);
    }

    /**
     * @return \Ghobaty\Billing\Plan
     */
    protected function plan(): Plan
    {
        if (
            ! request()->get('plan')
            || ! ($plan = Plan::byName((string)request()->get('plan')))
            || $plan->isFree()
            || $plan->isInactive()
        ) {
            throw ValidationException::withMessages(['plan' => ['The specified billing plan is invalid or inactive.']]);
        }

        return $plan;
    }

    /**
     * TODO more advanced logic to get this
     *
     * @return \Laravel\Cashier\Billable
     */
    protected function billable()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Auth::user();
    }

}
