<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BlogVisibilityOption;
use Illuminate\Support\Facades\Validator;

class BlogVisibilityOptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Only admins can manage visibility options
        $this->middleware(function ($request, $next) {
            if (auth()->user()->type !== 'admin') {
                abort(403, 'Unauthorized');
            }
            return $next($request);
        });
    }

    /**
     * List all visibility options (for admin management page).
     */
    public function index(Request $request)
    {
        $options = BlogVisibilityOption::getAll();
        $allowedKeys = BlogVisibilityOption::$allowedFieldKeys;

        $layout = $request->route('layout', 'side-menu');
        $theme  = $request->route('theme', 'light');

        return view('super-admin.blog.visibility-options', [
            'options'     => $options,
            'allowedKeys' => $allowedKeys,
            'layout'      => $layout,
            'theme'       => $theme,
            'title'       => 'Blog Visibility Options',
            'breadcrumb'  => '<a href="' . url('/') . '" class="breadcrumb">Dashboard</a>
                              <i data-feather="chevron-right" class="breadcrumb__icon"></i>
                              <a href="#" class="breadcrumb--active">Blog Visibility Options</a>',
        ]);
    }

    /**
     * Save (bulk create/update/delete) visibility options.
     * Expects JSON body: { options: [ {id?, label, field_key, color_class, is_active, sort_order}, ... ] }
     */
    public function save(Request $request)
    {
        try {
            $data = $request->json()->all();
            $rows = isset($data['options']) && is_array($data['options']) ? $data['options'] : [];

            if (empty($rows)) {
                return response()->json(['status' => false, 'message' => 'No options provided.']);
            }

            $allowedKeys = array_keys(BlogVisibilityOption::$allowedFieldKeys);
            $usedKeys = [];
            $savedIds = [];

            foreach ($rows as $row) {
                $label     = isset($row['label']) ? trim($row['label']) : '';
                $fieldKey  = isset($row['field_key']) ? trim($row['field_key']) : '';
                $colorClass = isset($row['color_class']) ? trim($row['color_class']) : 'bg-theme-1';
                $isActive  = isset($row['is_active']) ? (int) $row['is_active'] : 1;
                $sortOrder = isset($row['sort_order']) ? (int) $row['sort_order'] : 0;

                if ($label === '' || $fieldKey === '') {
                    continue; // skip blank rows
                }

                if (!in_array($fieldKey, $allowedKeys, true)) {
                    return response()->json([
                        'status'  => false,
                        'message' => "Invalid field key: {$fieldKey}. Allowed: " . implode(', ', $allowedKeys),
                    ]);
                }

                // Prevent duplicate field_key assignment in the same save operation
                if (in_array($fieldKey, $usedKeys, true)) {
                    return response()->json([
                        'status'  => false,
                        'message' => "Duplicate field_key '{$fieldKey}'. Each visibility option must map to a unique field.",
                    ]);
                }
                $usedKeys[] = $fieldKey;

                if (!empty($row['id'])) {
                    // Update existing
                    $option = BlogVisibilityOption::find((int) $row['id']);
                    if ($option) {
                        $option->update([
                            'label'       => $label,
                            'field_key'   => $fieldKey,
                            'color_class' => $colorClass,
                            'is_active'   => $isActive,
                            'sort_order'  => $sortOrder,
                        ]);
                        $savedIds[] = $option->id;
                    }
                } else {
                    // Create new
                    $option = BlogVisibilityOption::create([
                        'label'       => $label,
                        'field_key'   => $fieldKey,
                        'color_class' => $colorClass,
                        'is_active'   => $isActive,
                        'sort_order'  => $sortOrder,
                    ]);
                    $savedIds[] = $option->id;
                }
            }

            // Delete options that were removed by admin (not in saved list)
            if (!empty($savedIds)) {
                BlogVisibilityOption::whereNotIn('id', $savedIds)->delete();
            }

            return response()->json([
                'status'  => true,
                'message' => 'Visibility options saved successfully.',
                'options' => BlogVisibilityOption::getAll(),
            ]);
        } catch (\Exception $e) {
            \Log::error('BlogVisibilityOption save error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Toggle active/inactive for a single option.
     */
    public function toggleActive(Request $request, $id)
    {
        try {
            $option = BlogVisibilityOption::findOrFail($id);
            $option->is_active = $option->is_active ? 0 : 1;
            $option->save();
            return response()->json([
                'status'    => true,
                'is_active' => $option->is_active,
                'message'   => $option->is_active ? 'Option activated.' : 'Option deactivated.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Delete a single visibility option.
     */
    public function destroy($id)
    {
        try {
            BlogVisibilityOption::findOrFail($id)->delete();
            return response()->json(['status' => true, 'message' => 'Option deleted.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }
}
