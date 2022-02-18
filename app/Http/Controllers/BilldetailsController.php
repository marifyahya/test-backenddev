<?php

namespace App\Http\Controllers;

class BilldetailsController extends Controller
{
    // Get Billing Detail
    /**
     * @OA\Get(
     *     path="/api/billings-detail",
     *     summary="Billings Detail",
     *     tags={"BilldetailsController"},
     *     @OA\SecurityScheme(
     *          securityScheme="bearerAuth",
     *          type="http",
     *          scheme="bearer"
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={100000,150000,200000}, summary="An result object."),
     *         )
     *     ),
     * )
     */
    public function getBillingsDetail()
    {
        // Read file json
        $json = file_get_contents(storage_path('json/filter-data.json'));
        $json_data = json_decode($json, true);
        $billinsDetail = collect($json_data['data']['response']['billdetails']);

        // Map data body denom
        $denoms = $billinsDetail->map(function ($item) {
            $body = $item['body'][0];
            $bodyExtract = explode(':',$body);
            
            return intval($bodyExtract[1]);
        });

        // Filter denoms where rather than equal 100.0000
        $denomsFilter = [];
        foreach ($denoms as $item) {
            if ($item >= 100000) $denomsFilter[] = $item;
        }

        // result
        return $denomsFilter;
    }
}
