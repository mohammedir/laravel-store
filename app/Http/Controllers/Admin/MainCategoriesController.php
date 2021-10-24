<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MainCategoryRequest;
use App\Models\MainCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use DB;
use Illuminate\Support\Str;

class MainCategoriesController extends Controller
{
    //
    public function index(){

        $default_lang = get_default_lang();
        $categories = MainCategory::where('translation_lang', $default_lang)
            ->selection()
            ->get();

        return view('admin.maincategories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.maincategories.create');
    }

    public function store(MainCategoryRequest $request)
    {

        try {
            //return $request;

            $main_categories = collect($request->category);

            $filter = $main_categories->filter(function ($value, $key) {
                return $value['abbr'] == get_default_lang();
            });
            // حتى احفظ المتغير من غير رقم لانو return الي فوق بترجع القيمة مع رقم
            $default_category = array_values($filter->all()) [0];


            $filePath = "";
            if ($request->has('photo')) {

                $filePath = uploadImage('maincategories', $request->photo);
            }
            //اي يجب على جيمع الاوامر اسفل هذا الجملة ان تتنفذ
            DB::beginTransaction();

            $default_category_id = MainCategory::insertGetId([
                'translation_lang' => $default_category['abbr'],
                'translation_of' => 0,
                'name' => $default_category['name'],
                'slug' => $default_category['name'],
                'photo' => $filePath
            ]);
            // تقوم يارجاع كل اللغات الغير ديفلت
            $categories = $main_categories->filter(function ($value, $key) {
                return $value['abbr'] != get_default_lang();
            });

            // بافحص انه موجود لغات بالفعل غير اللغة الاساسية
            if (isset($categories) && $categories->count()) {

                $categories_arr = [];
                foreach ($categories as $category) {
                    $categories_arr[] = [
                        'translation_lang' => $category['abbr'],
                        'translation_of' => $default_category_id,
                        'name' => $category['name'],
                        'slug' => $category['name'],
                        'photo' => $filePath
                    ];
                }

                MainCategory::insert($categories_arr);
            }
            // اي نقم بحفظ جميع الكومينت في الداتا بيز
            DB::commit();

            return redirect()->route('admin.maincategories')->with(['success' => 'تم الحفظ بنجاح']);

        } catch (\Exception $ex) {
            DB::rollback();
            return redirect()->route('admin.maincategories')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);
        }

    }
    public function edit($mainCat_id)
    {
        //get specific categories and its translations
        $mainCategory = MainCategory::with('categories')
            ->selection()
            ->find($mainCat_id);

        if (!$mainCategory)
            return redirect()->route('admin.maincategories')->with(['error' => 'هذا القسم غير موجود ']);

        return view('admin.maincategories.edit', compact('mainCategory'));
    }


    public function update($mainCat_id, MainCategoryRequest $request)
    {


        try {
            $main_category = MainCategory::find($mainCat_id);

            if (!$main_category)
                return redirect()->route('admin.maincategories')->with(['error' => 'هذا القسم غير موجود ']);

            // update date

            $category = array_values($request->category) [0];

            if (!$request->has('category.0.active'))
                $request->request->add(['active' => 0]);
            else
                $request->request->add(['active' => 1]);


            MainCategory::where('id', $mainCat_id)
                ->update([
                    'name' => $category['name'],
                    'active' => $request->active,
                ]);

            // save image

            if ($request->has('photo')) {
                $filePath = uploadImage('maincategories', $request->photo);
                MainCategory::where('id', $mainCat_id)
                    ->update([
                        'photo' => $filePath,
                    ]);
            }


            return redirect()->route('admin.maincategories')->with(['success' => 'تم ألتحديث بنجاح']);
        } catch (\Exception $ex) {

            return redirect()->route('admin.maincategories')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);
        }

    }

}
