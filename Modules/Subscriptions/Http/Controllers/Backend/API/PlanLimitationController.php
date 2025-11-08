<?php

namespace Modules\Subscriptions\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Modules\Subscriptions\Http\Requests\PlanLimitationRequest;
use Modules\Subscriptions\Models\PlanLimitation;
use Modules\Subscriptions\Transformers\PlanLimitationResource;

class PlanLimitationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index()
    {
        return $this->sendResponse(PlanLimitationResource::collection(PlanLimitation::get()), __('messages.planlimitation_list'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Renderable
     */
    public function store(PlanLimitationRequest $request)
    {
        $planlimitation = PlanLimitation::create($request->all());

        return $this->sendResponse(new PlanLimitationResource($planlimitation), __('messages.planlimitation_create'));
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function show(PlanLimitation $planlimitation)
    {
        return $this->sendResponse(new PlanLimitationResource($planlimitation), __('messages.planlimitation_detail'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Renderable
     */
    public function update(PlanLimitationRequest $request, PlanLimitation $planlimitation)
    {
        $planlimitation->update($request->all());

        return $this->sendResponse(new PlanLimitationResource($planlimitation), __('messages.planlimitation_detail_updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function destroy(PlanLimitation $planlimitation)
    {
        $id = $planlimitation->id;
        $planlimitation->delete();

        return $this->sendResponse($id, __('messages.planlimitation_delete'));
    }
}
