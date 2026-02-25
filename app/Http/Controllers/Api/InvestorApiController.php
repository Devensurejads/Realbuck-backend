<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Investor;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Validator;

class InvestorApiController extends Controller
{
    private function normalizeMulti($value)
    {
        if (is_array($value)) {
            $items = array_filter(array_map('trim', $value), function ($item) {
                return $item !== '';
            });
            $items = array_unique($items);
            return implode(',', $items);
        }

        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

public function addupdateinvestor(Request $request)
    {
        try {
            $rules = array(
                'fullName' => 'required',
                'email' => 'required',
                'countryCode' => 'required',
                'phoneNumber' => 'required',
                'password' => 'required_without:id',
                'propertyType' => 'required',
                'location' => 'required',
                'investmentAmount' => 'required',
            );

            $fieldNames = array(
                'fullName' => 'fullName',
                'email' => 'email',
                'countryCode' => 'countryCode',
                'phoneNumber' => 'phoneNumber',
                'password' => 'password',
                'propertyType' => 'propertyType',
                'location' => 'location',
                'investmentAmount' => 'investmentAmount',
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                return $this->apiResponse([
                    'status' => 'failed',
                    'mesg' => $validator->errors()->first()
                ], 200);
            }

            $investor = null;
            $message = 'data added successfully';

            if (isset($request->id) && $request->id > 0) {
                $investor = Investor::find($request->id);
                if ($investor === null) {
                    return $this->apiResponse(['status' => 'failed', 'mesg' => 'Invalid form id '], 200);
                }
                $message = 'data updated successfully';
            } else {
                $investor = new Investor();
            }

            $investor->full_name = $request->fullName;
            $investor->email = $request->email;
            $investor->country_code = $request->countryCode;
            $investor->phone_number = $request->phoneNumber;
            $investor->property_type = $this->normalizeMulti($request->propertyType);
            $investor->location = $this->normalizeMulti($request->location);
            $investor->investment_amount = $request->investmentAmount;
            $investor->referred_by = $request->referredBy ?? null;

            if (isset($request->status) && in_array($request->status, ['Active', 'Inactive'], true)) {
                $investor->status = $request->status;
            }

            if (!empty($request->password)) {
                $investor->password = Hash::make($request->password);
            }

            $investor->save();

            $tokenName = 'investor-login';
            $token = $investor->createToken($tokenName)->plainTextToken;
            $expiresInMinutes = (int) config('sanctum.expiration');
            $expiresAt = $expiresInMinutes > 0 ? Carbon::now()->addMinutes($expiresInMinutes) : null;

            return $this->apiResponse([
                'status' => 'success',
                'data' => $investor->id,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt ? $expiresAt->toDateTimeString() : null,
                'mesg' => $message
            ], 200);
        } catch (\Exception $e) {
            return $this->apiResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getinvestorbyid(Request $request)
    {
        try {
            $rules = array(
                'id' => 'required',
            );

            $fieldNames = array(
                'id' => 'id',
            );
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                return $this->apiResponse([
                    'status' => 'failed',
                    'mesg' => $validator->errors()->first()
                ], 200);
            }

            $investor = Investor::find($request->id);
            if ($investor === null) {
                return $this->apiResponse(['status' => 'failed', 'mesg' => 'Invalid form id '], 200);
            }

            return $this->apiResponse([
                'status' => 'success',
                'data' => $investor,
                'mesg' => 'data fetched successfully'
            ], 200);
        } catch (\Exception $e) {
            return $this->apiResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getinvestorlist(Request $request)
    {
        try {
            $rules = array(
                'pageNo' => 'required',
                'pageSize' => 'required',
            );

            $fieldNames = array(
                'pageNo' => 'pageNo',
                'pageSize' => 'pageSize',
            );
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                return $this->apiResponse([
                    'status' => 'failed',
                    'mesg' => $validator->errors()->first()
                ], 200);
            }

            $query = Investor::orderBy('id', 'desc');
            if (isset($request->status) && $request->status !== '') {
                $query = $query->where('status', $request->status);
            }

            $data = $query->paginate(
                max((int) $request->pageSize, 10),
                ['*'],
                'page',
                max((int) $request->pageNo, 1)
            );

            return $this->apiResponse([
                'status' => 'success',
                'data' => $data,
                'mesg' => 'data fetched successfully'
            ], 200);
        } catch (\Exception $e) {
            return $this->apiResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteinvestorbyid(Request $request)
    {
        try {
            $rules = array(
                'id' => 'required',
            );

            $fieldNames = array(
                'id' => 'id',
            );
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                return $this->apiResponse([
                    'status' => 'failed',
                    'mesg' => $validator->errors()->first()
                ], 200);
            }

            $deleted = Investor::where('id', $request->id)->delete();

            if ($deleted) {
                return $this->apiResponse([
                    'status' => 'success',
                    'data' => $deleted,
                    'mesg' => 'Record deleted successfully'
                ], 200);
            }

            return $this->apiResponse([
                'status' => 'error',
                'data' => $deleted,
                'mesg' => 'No record found to delete'
            ], 404);
        } catch (\Exception $e) {
            return $this->apiResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateinvestorstatus(Request $request)
    {
        try {
            $rules = array(
                'formId' => 'required',
                'status' => 'required',
            );

            $fieldNames = array(
                'formId' => 'formId',
                'status' => 'status',
            );
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                return $this->apiResponse([
                    'status' => 'failed',
                    'mesg' => $validator->errors()->first()
                ], 200);
            }

            if (!in_array($request->status, ['Active', 'Inactive'], true)) {
                return $this->apiResponse([
                    'status' => 'failed',
                    'mesg' => 'Please enter correct status'
                ], 200);
            }

            $investor = Investor::find($request->formId);
            if ($investor === null) {
                return $this->apiResponse(['status' => 'failed', 'mesg' => 'Invalid form id '], 200);
            }

            $investor->status = $request->status;
            $investor->save();

            return $this->apiResponse([
                'status' => 'success',
                'data' => $investor->id,
                'mesg' => 'Status Update successfully'
            ], 200);
        } catch (\Exception $e) {
            return $this->apiResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function investorlogin(Request $request)
    {
        try {
            $rules = array(
                'email' => 'required',
                'password' => 'required',
            );

            $fieldNames = array(
                'email' => 'email',
                'password' => 'password',
            );
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails()) {
                return $this->apiResponse([
                    'status' => 'failed',
                    'mesg' => $validator->errors()->first()
                ], 200);
            }

            $investor = Investor::where('email', $request->email)->first();
            if ($investor === null) {
                return $this->apiResponse(['status' => 'failed', 'mesg' => 'Invalid email or password'], 200);
            }

            if (!Hash::check($request->password, $investor->password)) {
                return $this->apiResponse(['status' => 'failed', 'mesg' => 'Invalid email or password'], 200);
            }

            if ($investor->status === 'Inactive') {
                return $this->apiResponse(['status' => 'failed', 'mesg' => 'Account inactive'], 200);
            }

            $tokenName = 'investor-login';
            $token = $investor->createToken($tokenName)->plainTextToken;
            $expiresInMinutes = (int) config('sanctum.expiration');
            $expiresAt = $expiresInMinutes > 0 ? Carbon::now()->addMinutes($expiresInMinutes) : null;

            return $this->apiResponse([
                'status' => 'success',
                'data' => $investor,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt ? $expiresAt->toDateTimeString() : null,
                'mesg' => 'Login successful'
            ], 200);
        } catch (\Exception $e) {
            return $this->apiResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
