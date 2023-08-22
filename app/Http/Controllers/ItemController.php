<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use App\Models\ItemImagePivot;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ItemStoreRequest;
use App\Http\Requests\ItemUpdateRequest;

// KEY : DROPZONE
class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->view('itemindex', [
            'items' => Item::orderBy('updated_at', 'desc')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('itemcreate');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ItemStoreRequest $request)
    {
        $created = Item::create(['name' => $request->name, 'sku' => $request->sku, 'price' => $request->price]);
        foreach ($request->input('document', []) as $file) {
            //your file to be uploaded insert to database
            ItemImagePivot::create(['item_id' => $created->id, 'image' => $file]);
        }
        if ($created) { // inserted success
            return redirect()->route('item.index')
                ->withSuccess('Created successfully...!');
        }
        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'fails not created..!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        $item->with('getImagesHasMany')->where('id',$item->id)->first();
        return view('itemshow',compact('item'));        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id = "")
    {
        $item = Item::with('getImagesHasMany')->where('id', $id)->first();
        return view('itemedit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ItemUpdateRequest $request, Item $item)
    {
        $item->update($request->all());
        if ($request->has('document') && sizeof($request->get('document')) > 0) {

            $media = ItemImagePivot::where('item_id', $item->id)->pluck('image')->toArray();

            foreach ($request->input('document', []) as $file) {
                if (count($media) === 0 || !in_array($file, $media)) {
                    ItemImagePivot::create(['item_id' => $item->id, 'image' => $file]);
                }
            }
        }
        return redirect()->route('item.index')
            ->withSuccess('Updated Successfully...!');
    }

    /**
     * Remove the specified resource from storage and table.
     */
    public function destroy(Item $item)
    {
        // delete item images from storage folder and item image table
        $itemImages = ItemImagePivot::where('item_id', $item->id)->get();
        if (isset($itemImages) && !empty($itemImages)) {
            foreach ($itemImages as $imgVal) {                
                $path = storage_path('app/public/images/') . $imgVal['image'];
                //$path = public_path() . '/images/' . $request->filename;
                if (file_exists($path)) {
                    ItemImagePivot::where('image', $imgVal['image'])->delete();
                    unlink($path);
                }
            } // Loops Ends
        }
        $item->delete();
        return redirect()->route('item.index')
            ->withSuccess('Deleted Successfully.');
    }

    /**
     * Upload the specified resource to storage.
     */
    public function uploads(Request $request)
    {
        // $path = storage_path('tmp/uploads');
        $path = storage_path('app/public/images');
        !file_exists($path) && mkdir($path, 0777, true);
        $file = $request->file('file');
        //$name = uniqid() . '_' . trim($file->getClientOriginalName());
        $name = $file->getClientOriginalName();
        $file->move($path, $name);
        return response()->json([
            'name' => $name,
            'original_name' => $file->getClientOriginalName(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function fileDestroy(Request $request)
    {
        $path = storage_path('app/public/images/') . $request->filename;
        //$path = public_path() . '/images/' . $request->filename;
        if (file_exists($path)) {
            ItemImagePivot::where('image', $request->filename)->delete();
            unlink($path);
        }
        return $request->filename;
    }

    /**
     * Read the files resource from storage.
     */
    public function readFiles($id = "")
    {
        $images = ItemImagePivot::where('item_id', $id)->get()->toArray();
        $data = [];
        if (isset($images) && !empty($images)) {
            foreach ($images as $image) {
                $tableImages[] = $image['image'];
            }
            $storeFolder = storage_path('app/public/images');
            $file_path = storage_path('app/public/images/');
            $files = scandir($storeFolder);
            $data = [];
            foreach ($files as $file) {
                if ($file != '.' && $file != '..' && in_array($file, $tableImages)) {
                    $obj['name'] = $file;
                    $file_path = storage_path('app/public/images/') . $file;
                    $obj['size'] = filesize($file_path);
                    $obj['path'] = asset('storage/images/' . $file);
                    $data[] = $obj;
                }

            }
        }
        return response()->json($data);
    }

}