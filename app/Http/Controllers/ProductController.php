<?php

namespace App\Http\Controllers;

use App\Models\Commerce;
use App\Models\Product;
use App\Models\Rubro;
use App\Models\Subrubro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\File;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Commerce  $commerce
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Commerce $commerce)
    {
        //! no se porque arroja error
        //return Product::commerce($commerce)->with(['subrubro.rubro'])->get();

        return Product::with(['subrubro.rubro'])->whereCommerceId($commerce->id)->get();
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Product $product)
    {
        return Product::with(['subrubro.rubro'])->find($product->id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Commerce  $commerce
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Commerce $commerce)
    {
        $validatedData = $request->validate([
            'code' => 'required',
            'name' => 'required|max:255',
            'rubro_id' => 'required|exists:rubros,id',
            'subrubro_id' => 'sometimes|required|exists:subrubros,id',
            'price' => 'numeric',
            'description' => 'max:255',
        ]);

        $product = new Product();

        $rubro = Rubro::find($validatedData['rubro_id']);

        if (!array_key_exists('subrubro_id', $validatedData)) {

            $subrubro = new Subrubro();
            $subrubro->name = $request->post('subrubro');

            $subrubro->rubro()->associate($rubro);

            $subrubro->save();
        }
        else {
            $subrubro = Subrubro::find($validatedData['subrubro_id']);
        }

        $subrubroId = $subrubro->id;

        $product->subrubro()->associate($subrubro);

        $commerce->rubros()->syncWithoutDetaching($validatedData['rubro_id']);
        $commerce->subrubros()->syncWithoutDetaching($subrubroId);

        $product->fill($validatedData);

        $product->commerce()->associate($commerce);

        $product->save();

        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //! usar esta forma
        //$product->fill($request);

        $product->code = $request->post('code');
        $product->name = $request->post('name');
        $product->description = $request->post('description');
        $product->price = $request->post('price');
        $product->disabled = $request->post('disabled');

        $product->save();

        return response($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        $product->delete();

        return response(true);
    }

    /**
     * Upload a product image/avatar to storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request, Product $product)
    {
        // todo: create request to validate it (size, extension...)

        if (!$request->hasFile('image') || !$request->file('image')->isValid()) {
            return;
        }

        $path = $request->file('image')->store('images', 'public');

        logger($path);

        $product->avatar_dirname = env('APP_URL') . '/storage/' . $path;
        $product->avatar = '';

        $product->save();

        return response($product);
    }
}
