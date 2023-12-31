<?php

namespace App\Http\Controllers;

use App\Http\Requests\GardenImageStoreRequest;
use App\Http\Resources\GardenCollection;
use App\Http\Resources\GardenImageCollection;
use App\Http\Resources\GardenImageResource;
use App\Models\Garden;
use App\Models\GardenImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Str;

class GardenImageController extends Controller
{
    public function index()
    {
        $with = [];

        if (isset($_GET['garden'])) {

            $with[] = 'garden';
        }

        $gardenImages = QueryBuilder::for(GardenImage::with($with))->paginate();

        return new GardenImageCollection($gardenImages);
    }

    public function store(GardenImageStoreRequest $request)
    {
        $validated = $request->validated();

        // START: SAVE IMAGE TO FILES
        $image_64 = $request->file;
        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
        $replace = substr($image_64, 0, strpos($image_64, ',')+1);

        $image = str_replace($replace, '', $image_64);
        $image = str_replace(' ', '+', $image);
        $imageName = Str::random(10) . uniqid() . '.' . $extension;
        Storage::put($imageName, base64_decode($image));
        // FINISH: SAVE IMAGE TO FILES

        $validated['file'] = $imageName;
        $gardenImage = GardenImage::create($validated);

        return new GardenImageResource($gardenImage);
    }

    public function show(Request $request, GardenImage $gardenImage)
    {
        if (isset($_GET['garden'])) {

            $gardenImage->load('garden');
        }

        return new GardenImageResource($gardenImage);
    }

    public function destroy(Request $request, GardenImage $gardenImage)
    {
        $gardenImage->delete();

        return response()->noContent();
    }
}
