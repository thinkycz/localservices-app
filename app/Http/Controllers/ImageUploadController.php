<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageUploadController extends Controller
{
    protected $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Upload images for a service.
     */
    public function upload(Request $request, int $serviceId): JsonResponse
    {
        $service = Service::where('user_id', $request->user()->id)
            ->findOrFail($serviceId);

        $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max
        ]);

        $uploadedImages = [];

        foreach ($request->file('images') as $index => $image) {
            // Generate unique filename
            $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
            $path = 'services/' . $service->id . '/' . $filename;

            // Store original image
            Storage::disk('public')->putFileAs(
                dirname($path),
                $image,
                $filename
            );

            // Create thumbnail
            $this->createThumbnail($image, $path);

            // Get image info
            $imageInfo = getimagesize($image->getPathname());

            // Create database record
            $serviceImage = ServiceImage::create([
                'service_id' => $service->id,
                'path' => $path,
                'filename' => $filename,
                'mime_type' => $image->getMimeType(),
                'size' => $image->getSize(),
                'order' => $service->images()->count() + $index,
                'is_primary' => $service->images()->count() === 0 && $index === 0,
                'alt_text' => $service->name,
            ]);

            $uploadedImages[] = [
                'id' => $serviceImage->id,
                'url' => $serviceImage->url,
                'thumbnail_url' => $serviceImage->thumbnail_url,
                'is_primary' => $serviceImage->is_primary,
                'order' => $serviceImage->order,
            ];
        }

        return response()->json([
            'success' => true,
            'images' => $uploadedImages,
            'message' => count($uploadedImages) . ' image(s) uploaded successfully',
        ]);
    }

    /**
     * Create thumbnail from uploaded image.
     */
    protected function createThumbnail($image, string $path): void
    {
        $thumbnailPath = dirname($path) . '/thumbs/' . basename($path);

        // Read and resize image
        $img = $this->imageManager->read($image->getPathname());
        $img->cover(300, 300);

        // Save thumbnail
        Storage::disk('public')->put(
            $thumbnailPath,
            $img->encode()
        );
    }

    /**
     * Delete an image.
     */
    public function destroy(Request $request, int $serviceId, int $imageId): JsonResponse
    {
        $service = Service::where('user_id', $request->user()->id)
            ->findOrFail($serviceId);

        $image = ServiceImage::where('service_id', $service->id)
            ->findOrFail($imageId);

        // Delete files from storage
        Storage::disk('public')->delete($image->path);
        Storage::disk('public')->delete(dirname($image->path) . '/thumbs/' . $image->filename);

        // Delete database record
        $image->delete();

        // If deleted image was primary, set new primary
        if ($image->is_primary) {
            $newPrimary = $service->images()->first();
            if ($newPrimary) {
                $newPrimary->update(['is_primary' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully',
        ]);
    }

    /**
     * Set image as primary.
     */
    public function setPrimary(Request $request, int $serviceId, int $imageId): JsonResponse
    {
        $service = Service::where('user_id', $request->user()->id)
            ->findOrFail($serviceId);

        $image = ServiceImage::where('service_id', $service->id)
            ->findOrFail($imageId);

        // Unset current primary
        $service->images()->update(['is_primary' => false]);

        // Set new primary
        $image->update(['is_primary' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Primary image updated',
        ]);
    }

    /**
     * Update image order.
     */
    public function updateOrder(Request $request, int $serviceId): JsonResponse
    {
        $service = Service::where('user_id', $request->user()->id)
            ->findOrFail($serviceId);

        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:service_images,id',
            'orders.*.order' => 'required|integer',
        ]);

        foreach ($request->orders as $orderData) {
            ServiceImage::where('service_id', $service->id)
                ->where('id', $orderData['id'])
                ->update(['order' => $orderData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Image order updated',
        ]);
    }

    /**
     * Get all images for a service.
     */
    public function index(Request $request, int $serviceId): JsonResponse
    {
        $service = Service::where('user_id', $request->user()->id)
            ->findOrFail($serviceId);

        $images = $service->images()->get()->map(function ($image) {
            return [
                'id' => $image->id,
                'url' => $image->url,
                'thumbnail_url' => $image->thumbnail_url,
                'is_primary' => $image->is_primary,
                'order' => $image->order,
                'alt_text' => $image->alt_text,
            ];
        });

        return response()->json([
            'success' => true,
            'images' => $images,
        ]);
    }
}
