<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CityController extends BaseController
{
    public function index(Request $request)
    {
        $keyword = $request->get('keyword', null);
        $cities = City::ofKeyword($keyword)->orderBy('id', 'desc')->paginate(10);

        return Inertia::render('Admin/cities/Index', [
            'cities' => $cities,
            'keyword' => $keyword,
        ]);
    }

    public function edit(string $id)
    {
        $city = City::findOrFail($id);

        return Inertia::render('Admin/cities/Edit', [
            'city' => $city
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'shamel_id' => ['required', 'string', 'max:255', Rule::unique('cities')->ignore($id)],
            'name_ar' => 'required|string|max:255',
            'name_heb' => 'required|string|max:255',
        ]);

        $city = City::findOrFail($id);
        $city->update($request->all());

        return redirect()->route('admin.cities.index')
            ->with('success', 'City updated successfully');
    }

    public function destroy(string $id)
    {
        $city = City::findOrFail($id);
        $city->delete();

        return redirect()->route('admin.cities.index')
            ->with('success', 'City deleted successfully');
    }
}