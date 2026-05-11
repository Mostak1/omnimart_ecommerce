<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\PoliceStation;
use Illuminate\Http\Request;

class PoliceStationController extends Controller
{
    public function index()
    {
        $police_stations = PoliceStation::orderBy('id', 'desc')->get();
        return view('back.police_station.index', compact('police_stations'));
    }

    public function create()
    {
        $districts = District::ordered()->get();
        return view('back.police_station.create', compact('districts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'district_id' => 'required',
            'name' => 'required|max:255',
        ]);

        PoliceStation::create($request->all());
        return redirect()->route('back.police_station.index')->withSuccess(__('Police Station Added Successfully.'));
    }

    public function edit(PoliceStation $police_station)
    {
        $districts = District::ordered()->get();
        return view('back.police_station.edit', compact('police_station', 'districts'));
    }

    public function update(Request $request, PoliceStation $police_station)
    {
        $request->validate([
            'district_id' => 'required',
            'name' => 'required|max:255',
        ]);

        $police_station->update($request->all());
        return redirect()->route('back.police_station.index')->withSuccess(__('Police Station Updated Successfully.'));
    }

    public function status(PoliceStation $police_station, $status)
    {
        $police_station->update(['status' => $status]);
        return redirect()->route('back.police_station.index')->withSuccess(__('Status Updated Successfully.'));
    }

    public function destroy(PoliceStation $police_station)
    {
        $police_station->delete();
        return redirect()->route('back.police_station.index')->withSuccess(__('Police Station Deleted Successfully.'));
    }
}
