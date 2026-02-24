<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::withCount('services')->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }
}
