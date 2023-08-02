<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use App\Models\ItemImagePivot;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ItemStoreRequest;

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
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id = "")
    {        
        $item = Item::with('getImagesHasMany')->where('id',$id)->first();
        return view('itemedit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        //
    }

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
        dd($request->all());
        $path = storage_path('tmp/uploads/') . $request->filename;
        //$path = public_path() . '/images/' . $request->filename;
        if (file_exists($path)) {
            // ItemImagePivot::where('filename',$request->filename)->delete();
            unlink($path);
        }
        return $request->filename;
    }

    /**
     * Read the files resource from storage.
     */
    public function readFilesxxx($id = "")
    {
        $directory = 'uploads';
        $files_info = [];
        $file_ext = array('png', 'jpg', 'jpeg', 'pdf');

        // Read files
        foreach (File::allFiles(public_path($directory)) as $file) {
            $extension = strtolower($file->getExtension());

            if (in_array($extension, $file_ext)) { // Check file extension 
                $filename = $file->getFilename();
                $size = $file->getSize(); // Bytes 
                $sizeinMB = round($size / (1000 * 1024), 2); // MB 

                if ($sizeinMB <= 2) { // Check file size is <= 2 MB 
                    $files_info[] = array(
                        "name" => $filename,
                        "size" => $size,
                        "path" => url($directory . '/' . $filename)
                    );
                }
            }
        }
        return response()->json($files_info);
    }

    public function readFiles($id = "")
    {
        $images = ItemImagePivot::where('item_id',$id)->get()->toArray();        
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
        //dd($data);
        return response()->json($data);
    }

}