<?php


namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use App\Http\Controllers\API\ImageUploadController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{

    protected $imageUploadController;

    public function __construct()
    {
        $this->imageUploadController = new ImageUploadController();
    }

    /**
     * Get User Data API
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserData()
    {
        $user = Auth::user();

        if (!$user) {
            return $this->sendError('User not authenticated.', [], 401);
        }

        return $this->sendResponse($user, 'User data retrieved successfully.');
    }

    /**
     * Update Profile API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|required|string|max:15',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->only(['first_name', 'last_name', 'email', 'phone']);
        $user->update($input);

        return $this->sendResponse($user, 'Profile updated successfully.');
    }

    /**
     * Upload Avatar API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadAvatar(Request $request)
    {
        $user = Auth::user();

        $result = $this->imageUploadController->uploadUserAvatar($request);

        if (array_key_exists('error', $result)) {
            return $this->sendError($result['error']);
        }

        $user->update(['avatar' => $result['file_path']]);

        $success['avatar_url'] = $result['file_path'];

        return $this->sendResponse($success, 'Avatar uploaded successfully.');
    }

    /**
     * Delete Avatar API
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAvatar()
    {
        $user = Auth::user();

        if (!$user->avatar) {
            return $this->sendError('No avatar found to delete.', [], 404);
        }

        $storagePath = str_replace('/images/', 'uploads/', $user->avatar);

        if (Storage::disk('public')->exists($storagePath)) {
            try {
                Storage::disk('public')->delete($storagePath);
                $user->avatar = null;
                $user->save();
                return $this->sendResponse([], 'Avatar deleted successfully.');
            } catch (\Exception $e) {
                return $this->sendError('Error deleting avatar.', ['error' => $e->getMessage()], 500);
            }
        } else {
            return $this->sendError('Avatar file not found.', [], 404);
        }
    }
    public function deleteUserById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::find($request->query('id'));

        // Delete user's avatar if it exists
        if ($user->avatar) {
            $avatarPath = "public/{$user->avatar}";
            if (Storage::exists($avatarPath)) {
                Storage::delete($avatarPath);
            }
        }

        // Perform account deletion
        $user->delete();

        return $this->sendResponse([], 'User account deleted successfully.');
    }
    /**
     * Get All Users API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers(Request $request)
    {
        $query = User::query();

        // Optional Filters
        if ($request->has('id')) {
            $query->where('id', $request->query('id'));
        }

        if ($request->has('search')) {
            $searchTerm = $request->query('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', $searchTerm)
                    ->orWhere('first_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->has('role')) {
            $query->where('role', $request->query('role'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->query('is_active'));
        }

        // Pagination
        $perPage = $request->query('per_page', 10); // Default 10 users per page
        $users = $query->paginate($perPage);

        return $this->sendResponse($users, 'Users retrieved successfully.');
    }
    public function disableUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::find($request->query('id'));

        // Check if the user is already disabled
        if (!$user->is_active) {
            return $this->sendError('User is already disabled.', [], 400);
        }

        // Disable the user
        $user->update(['is_active' => false]);

        return $this->sendResponse($user, 'User disabled successfully.');
    }
}
