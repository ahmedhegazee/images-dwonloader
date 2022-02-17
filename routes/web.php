<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get("/download-files", function (Request $request) {
    $data = $request->all(['start', 'end', 'extension', 'url']);
    $validator = validator()->make($data, [
        "start" => "required|integer|min:1|lt:end",
        "end" => "required|integer|gt:start",
        "extension" => ['required', 'string', 'in:svg,png,jpg,jpeg,webp'],
        'url' => "required|string|url"
    ]);
    if ($validator->fails()) {
        return $validator->errors()->toArray();
    }
    $zip = new ZipArchive();
    $zip->open(config("filesystems.disks.public.root") . "/images.zip", ZipArchive::CREATE);
    for ($i = $request->start; $i <= $request->end; $i++) {
        $image = file_get_contents($request->url . $i . '.' . $request->extension);
        Storage::disk("images")->put($i . '.' . $request->extension, $image);
        $zip->addFile(config("filesystems.disks.images.root") . "/$i." . $request->extension, "$i." . $request->extension);
    }
    $zip->close();
    return Response::download(config("filesystems.disks.public.root") . "/images.zip");
});