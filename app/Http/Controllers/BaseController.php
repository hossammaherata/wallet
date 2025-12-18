<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    /**
     * Save a model instance.
     *
     * @param  Request $request
     * @param  Model   $model
     * @param  array   $validationRules
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request, Model $model, array $validationRules)
    {
        $data = $request->validate($validationRules);

        $model->fill($data);
        $model->save();

        return redirect()->back()->with('success', $model->wasRecentlyCreated ? 'Created successfully!' : 'Updated successfully!');
    }

    /**
     * Delete a model instance.
     *
     * @param  Model $model
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Model $model)
    {
        $model->delete();

        return redirect()->back()->with('success', 'Deleted successfully!');
    }
}
