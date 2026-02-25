<?php
namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Models\{ContactUsEnquiry,Properties,PropertyEnquiries,PropertyImages,Settings,SellUser,BuySellUser,BuyUser,Agent};
use Validator;
use DB;
use Carbon\Carbon;
use Notification;

class PropertyApiController extends Controller
{
 protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }
    
//      public function getPropertyenquirylist(Request $request){
//          try {
//              $rules = array(
//             'pageNo'=>'required',
//             'pageSize'=>'required',
//         );

//         $fieldNames = array(
//             'pageNo'=>'pageNo',
//             'pageSize'=>'pageSize',
//         );
//         $validator = Validator::make($request->all(), $rules);
//         $validator->setAttributeNames($fieldNames);

//         if ($validator->fails()) {
//          return $this->apiResponse([
//             'status' => 'failed',
//             'mesg'   => $validator->errors()->first()
//         ], 200);
//         } else {
          

//     $data = PropertyEnquiries::where('is_email_verified', 1)->orderBy('id', 'desc')
//         ->paginate(
//             max((int) $request->pageSize, 10),
//             ['*'],
//             'page',
//             max((int) $request->pageNo, 1)
//         );


// return $this->apiResponse(['status' => 'success', 'data' => $data,'mesg'=>'data fetched  successfully'], 200);

//     }
//         }catch (Exception $qx) {
//           return $this->apiResponse([
//         'status' => 'failed',
//         'mesg'   => $e->getMessage()
//     ], 500);
//         }
//     }
    

 public function getPropertylist(Request $request){
         try {
             $rules = array(
            'pageNo'=>'required',
            'pageSize'=>'required',
            'type'=>'required',
        );

        $fieldNames = array(
            'pageNo'=>'pageNo',
            'pageSize'=>'pageSize',
               'type'=>'type',
        );
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails()) {
         return $this->apiResponse([
            'status' => 'failed',
            'mesg'   => $validator->errors()->first()
        ], 200);
        } else {
            
              if (!in_array($request->type, ['PROPERTY', 'PROPERTYENQUIRY'], true)) {
    return $this->apiResponse([
        'status' => 'failed',
        'mesg'   => 'Please enter correct type'
    ], 200);
} 
       // Map request type to model class
// Allowed types
$modelMap = [
    'PROPERTY'    => Properties::class,
    'PROPERTYENQUIRY' => PropertyEnquiries::class,
];


    $model = $modelMap[$request->type];
    
    if($request->type=='PROPERTYENQUIRY'){
        
        
    $data = $model::where('is_email_verified', 1);

if (isset($request->filtertype) && $request->filtertype != '') {
    $data = $data->where('enquiryStatus', $request->filtertype);
} else {
    // Optional: you can skip this else if you don't want a default filter
    $data = $data->where('enquiryStatus', $request->filtertype);
}

$data = $data->orderBy('id', 'desc')
    ->paginate(
        max((int) $request->pageSize, 10),
        ['*'],
        'page',
        max((int) $request->pageNo, 1)
    );


    }else{
         $data = Properties::with('images')->orderBy('id', 'desc')
        ->paginate(
            max((int) $request->pageSize, 10),
            ['*'],
            'page',
            max((int) $request->pageNo, 1)
        );
    }

 

return $this->apiResponse(['status' => 'success', 'data' => $data,'mesg'=>'data fetched  successfully'], 200);

    }
        }catch (Exception $qx) {
          return $this->apiResponse([
        'status' => 'failed',
        'mesg'   => $e->getMessage()
    ], 500);
        }
    }
    
    
    


 public function getactivePropertylist(Request $request){
         try {
             $rules = array(
            'pageNo'=>'required',
            'pageSize'=>'required',
        );

        $fieldNames = array(
            'pageNo'=>'pageNo',
            'pageSize'=>'pageSize',
        );
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails()) {
         return $this->apiResponse([
            'status' => 'failed',
            'mesg'   => $validator->errors()->first()
        ], 200);
        } else {
          

    $data = Properties::with('images')->where('PropertyStatus','Active')->orderBy('id', 'desc')
        ->paginate(
            max((int) $request->pageSize, 10),
            ['*'],
            'page',
            max((int) $request->pageNo, 1)
        );


return $this->apiResponse(['status' => 'success', 'data' => $data,'mesg'=>'data fetched  successfully'], 200);

    }
        }catch (Exception $qx) {
          return $this->apiResponse([
        'status' => 'failed',
        'mesg'   => $e->getMessage()
    ], 500);
        }
    }


 public function getactivePropertybyid(Request $request){
         try {
             $rules = array(
            'id'=>'required',
        );

        $fieldNames = array(
            'id'=>'id',
        );
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails()) {
         return $this->apiResponse([
            'status' => 'failed',
            'mesg'   => $validator->errors()->first()
        ], 200);
        } else {
          
    if(isset($request->id) && $request->id>0){

$userdata = Properties::find($request->id);
// dd($userdata->id );
if($userdata===null){
      return $this->apiResponse(['status' => 'failed','mesg'=>'Invalid form id '], 200);
}
    $data = Properties::with('images')->where('id',$request->id)->first();

}
return $this->apiResponse(['status' => 'success', 'data' => $data,'mesg'=>'data fetched  successfully'], 200);

    }
        }catch (Exception $qx) {
          return $this->apiResponse([
        'status' => 'failed',
        'mesg'   => $e->getMessage()
    ], 500);
        }
    }




    

        
         public function addupdateproperty(Request $request){
    try {

        $rules = array(
            'PropertyTitle' => 'required',
            'PropertyPrice' => 'required',
            'PropertyDescription' => 'required',
            'PropertyStatus' => 'required',
            'number_of_shares' => 'nullable|numeric|min:0',
            'minimum_investment_amount' => 'nullable|numeric|min:0',
        );

        $fieldNames = array(
            'PropertyTitle'=>'PropertyTitle',
            'PropertyPrice'=>'PropertyPrice',
            'PropertyDescription'=>'PropertyDescription',
            'PropertyStatus'=>'PropertyStatus',
        );

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails()) {
            return $this->apiResponse([
                'status' => 'failed',
                'mesg'   => $validator->errors()->first()
            ], 200);
        }

        /* =====================================================
           UPDATE PROPERTY
        ===================================================== */
        if(isset($request->id) && $request->id > 0){

            $mesg = 'data updated successfully';

            $userdata = Properties::find($request->id);

            if($userdata === null){
                return $this->apiResponse([
                    'status' => 'failed',
                    'mesg'=>'Invalid form id'
                ], 200);
            }

            // -------- BASIC FIELDS ----------
            $userdata->PropertyType = $request->PropertyType ?? null;
            $userdata->PropertyTitle = $request->PropertyTitle ?? null;
            $userdata->PropertyPrice = $request->PropertyPrice ?? '0';
            $userdata->PropertyBedrooms = $request->PropertyBedrooms ?? '0';
            $userdata->PropertyBathrooms = $request->PropertyBathrooms ?? '0';
            $userdata->PropertyArea = $request->PropertyArea ?? null;
            $userdata->PropertyYearBuilt = $request->PropertyYearBuilt ?? null;
            $userdata->PropertyLocation = $request->PropertyLocation ?? null;
            $userdata->PropertyDescription = $request->PropertyDescription ?? null;
            $userdata->PropertyDocument = $request->PropertyDocument ?? null;
            $userdata->PropertyStatus = $request->PropertyStatus;

            // ✅ NEW FIELDS
            $userdata->number_of_shares =
                $request->number_of_shares ?? 0;

            $userdata->minimum_investment_amount =
                $request->minimum_investment_amount ?? 0;

            $userdata->update();

            $userid = $request->id;

            /* ================= IMAGES SYNC ================= */

            $images = explode(',', $request->image_url ?? '');
            $images = array_unique(array_filter(array_map('trim',$images)));

            PropertyImages::where('property_id',$userid)
                ->whereNotIn('image_url',$images)
                ->delete();

            foreach ($images as $image_url) {
                $exists = PropertyImages::where('property_id',$userid)
                    ->where('image_url',$image_url)
                    ->exists();

                if(!$exists){
                    PropertyImages::create([
                        'property_id'=>$userid,
                        'image_url'=>$image_url
                    ]);
                }
            }

            /* ================= DOCUMENT SYNC ================= */

            $documents = explode(',', $request->PropertyDocument ?? '');
            $documents = array_unique(array_filter(array_map('trim',$documents)));

            PropertyImages::where('property_id',$userid)
                ->whereNotIn('PropertyDocuments',$documents)
                ->delete();

            foreach ($documents as $docUrl) {

                $exists = PropertyImages::where('property_id',$userid)
                    ->where('PropertyDocuments',$docUrl)
                    ->exists();

                if(!$exists){
                    PropertyImages::create([
                        'property_id'=>$userid,
                        'PropertyDocuments'=>$docUrl
                    ]);
                }
            }

        }

        /* =====================================================
           CREATE PROPERTY
        ===================================================== */
        else{

            $mesg = 'data added successfully';

            $userdata = new Properties;

            $userdata->PropertyType = $request->PropertyType ?? null;
            $userdata->PropertyTitle = $request->PropertyTitle ?? null;
            $userdata->PropertyPrice = $request->PropertyPrice ?? '0';
            $userdata->PropertyBedrooms = $request->PropertyBedrooms ?? '0';
            $userdata->PropertyBathrooms = $request->PropertyBathrooms ?? '0';
            $userdata->PropertyArea = $request->PropertyArea ?? null;
            $userdata->PropertyYearBuilt = $request->PropertyYearBuilt ?? null;
            $userdata->PropertyLocation = $request->PropertyLocation ?? null;
            $userdata->PropertyDescription = $request->PropertyDescription ?? null;
            $userdata->PropertyDocument = $request->PropertyDocument ?? null;

            // ✅ NEW FIELDS (CREATE)
            $userdata->number_of_shares =
                $request->number_of_shares ?? 0;

            $userdata->minimum_investment_amount =
                $request->minimum_investment_amount ?? 0;

            $userdata->save();

            $userid = $userdata->id;

            /* ================= SAVE IMAGES ================= */

            if(!empty($request->image_url)){
                $images = explode(',', $request->image_url);
                $images = array_unique(array_filter(array_map('trim',$images)));

                foreach($images as $imageUrl){
                    PropertyImages::create([
                        'property_id'=>$userid,
                        'image_url'=>$imageUrl
                    ]);
                }
            }

            /* ================= SAVE DOCUMENTS ================= */

            if(!empty($request->PropertyDocument)){
                $documents = explode(',', $request->PropertyDocument);
                $documents = array_unique(array_filter(array_map('trim',$documents)));

                foreach($documents as $docUrl){
                    PropertyImages::create([
                        'property_id'=>$userid,
                        'PropertyDocuments'=>$docUrl
                    ]);
                }
            }
        }

        /* ===================================================== */

        if($userid > 0){
            return $this->apiResponse([
                'status'=>'success',
                'data'=>$userid,
                'mesg'=>$mesg
            ],200);
        }

        return $this->apiResponse([
            'status'=>'failed',
            'mesg'=>'Some error occur'
        ],200);

    } catch (Exception $qx) {

        return $this->apiResponse([
            'status'=>'error',
            'message'=>$qx->getMessage()
        ],500);
    }
}


     public function propertyenquiry(Request $request){
         try {
             $rules = array(
            'firstName'=>'required',
            'lastName'=>'required',
            'email'=>'required',
            'phoneNumber'=>'required',
            'countryCode'=>'required',
            'propertyId'=>'required',

        );

        $fieldNames = array(
            'firstName'=>'firstName',
            'lastName'=>'lastName',
            'email'=>'email',
            'phoneNumber'=>'phoneNumber',
            'countryCode'=>'countryCode',
            'propertyId'=>'propertyId',
        );
     
         $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

    if ($validator->fails()) {
          return $this->apiResponse([
            'status' => 'failed',
            'mesg'   => $validator->errors()->first()
        ], 200);
    } else {
        $otp=$this->helper->generatePIN(4);
       
    if(isset($request->id) && $request->id>0){

$userdata = PropertyEnquiries::find($request->id);
// dd($userdata->id );
if($userdata===null){
      return $this->apiResponse(['status' => 'failed','mesg'=>'Invalid form id '], 200);
}
$userdata->firstName   = $request->firstName;
$userdata->lastName   =$request->lastName;
$userdata->email   = $request->email;
$userdata->phoneNumber   =$request->phoneNumber;
$userdata->countryCode   =$request->countryCode;
$userdata->propertyId   =$request->propertyId;
$userdata->email_otp  =$otp;
$userdata->update();
$userid=$request->id;
    }else{
         $userdata = new PropertyEnquiries;
$userdata->firstName   = $request->firstName;
$userdata->lastName   =$request->lastName;
$userdata->email   = $request->email;
$userdata->phoneNumber   =$request->phoneNumber;
$userdata->countryCode   =$request->countryCode;
$userdata->propertyId   =$request->propertyId;
$userdata->email_otp  =$otp;
$userdata->save();  
$userid=$userdata->id;
    }


    
if($userid >0){
 $usr_subject='Your OTP for verification with RealBucs';
 $otpdata=array('name'=> $request->firstName,
 'email'=>$request->email,
 'otp'=>$otp);
 $html = view('mailer.otpmailer')->with(compact('otpdata'))->render();
 $usr_message= $html ;
 $attach='';
//  dd($html);
 sentmailsmtp($request->email,$usr_subject,$usr_message, $attach);
 $data=array(
'id'=>$userid
 );
 return $this->apiResponse(['status' => 'success', 'data' =>$data ,'mesg'=>'Property Enquiry submitted successfully'], 200);
}

return $this->apiResponse(['status' => 'failed', 'mesg'=>'Some error occur'], 200);


    }
        }catch (Exception $qx) {
             return $this->apiResponse([
        'status' => 'error',
        'message' =>  $qx
    ], 500);
        }
    }
    
      

      
   

}



