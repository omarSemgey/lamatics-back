<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page = request()->get('page', 1);
        $cacheKey = 'users_page_' . $page;
        $minutes = 60; 

        $users = Cache::tags(['users_list'])->remember($cacheKey, $minutes * 60, function () {
            return User::withCount('userSubmissions')->paginate(10);
        });
    
        return response()->json([
            'status' => 'success',
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'role' => $validatedData['role'],
                'password' => Hash::make($validatedData['password']),
            ]);

            DB::commit();

            Cache::forget('counts');
            Cache::forget('user_counts');
            Cache::tags(['users_list', 'user_searches'])->flush();

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
            ]);

        } catch (\Throwable $err) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'User creation failed: ' . $err->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $cacheKey = 'user_' . $id;
        $hours = 1;

        $user = Cache::remember($cacheKey, $hours * 3600, function () use ($id) {
            return User::withCount('userSubmissions')->findOrFail($id);
        });

        if($user == null){
            return response()->json([
                'status' => 'error',
                'message' => 'user doesnt exist',
            ]);
        }
        return response()->json([
            'status' => 'success',
            'user' => $user,
        ]);
    }

    /**
     * Search for a user
     */
    public function search(Request $request)
    {
        try {
            $searchTerm = str_replace(['%', '_'], '', $request->input('search', ''));
            $searchTerm = trim($searchTerm);

            $validator = Validator::make(
                [ 'search' => $searchTerm ],
                [ 'search' => 'required|string|max:25', ]
            );

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $validatedSearch = $validator->validated();

            $searchTerm = $validatedSearch['search'];
            $page = request()->get('page', 1);
            $cacheKey = 'user_search_' . md5($searchTerm . '_page_' . $page);
            $minutes = 15;

            $users = Cache::tags(['user_searches'])->remember($cacheKey, $minutes * 60, function () use ($searchTerm) {
                return User::withCount('userSubmissions')
                ->where('name', 'LIKE', "%{$searchTerm}%")
                ->orderByRaw('
                    CASE 
                        WHEN name LIKE ? THEN 1
                        WHEN name LIKE ? THEN 2
                        WHEN name LIKE ? THEN 3
                        ELSE 4
                    END ASC
                    ', [$searchTerm, $searchTerm.'%', '%'.$searchTerm.'%']
                )
                ->paginate(10);
            });


            return response()->json([
                'status' => 'success',
                'users' => $users
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Search failed',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            $updateData = [];

            if (isset($validated['name'])) {
                $updateData['name'] = $validated['name'];
            }

            if (isset($validated['email'])) {
                $updateData['email'] = $validated['email'];
            }

            if (isset($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            if(!empty($updateData)) User::findOrFail($id)->update($updateData);

            User::findOrFail($id)->update($validatedData);

            DB::commit();

            Cache::forget('user_' . $id);
            Cache::tags(['users_list', 'user_searches'])->flush();

            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully',
            ]);

        } catch (\Throwable $err) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Update failed: ' . $err->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if($user == null){
            return response()->json([
                'status' => 'error',
                'message' => 'user doesnt exist',
            ]);
        }

        try{
            DB::beginTransaction();

            $user->delete();

            DB::commit();

            Cache::forget('counts');
            Cache::forget('user_counts');
            Cache::forget('user_' . $id);
            Cache::tags(['users_list', 'user_searches'])->flush();

            return response()->json([
                'status' => 'success',
                'message' => 'user deleted successfully'
            ]);
        }catch(\Throwable $err){
            DB::rollBack();
            return response()->json([
                'status' => $err,
            ]);
        };
    }
}
