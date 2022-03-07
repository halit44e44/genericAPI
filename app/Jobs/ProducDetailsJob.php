<?php

namespace App\Jobs;

use App\Helpers\GenbaFunctionsHelper;
use App\Models\Genba\GenbaAgeRestrictions;
use App\Models\Genba\GenbaDevelopers;
use App\Models\Genba\GenbaGameLanguages;
use App\Models\Genba\GenbaGraphics;
use App\Models\Genba\GenbaInstructions;
use App\Models\Genba\GenbaLanguages;
use App\Models\Genba\GenbaMetaData;
use App\Models\Genba\GenbaProductDetails;
use App\Models\Genba\GenbaProducts;
use App\Models\Genba\GenbaPublishers;
use App\Models\Genba\GenbaRestrictions;
use App\Models\Genba\GenbaVideoUrls;
use App\Models\Genres;
use App\Models\Platforms;
use Carbon\Carbon;
use Exception;

class ProducDetailsJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $acceptableLanguages = ['English', 'Turkish'];
        try {
            $genbaProducts = GenbaProducts::groupBy('productId')
            ->limit(10)
            ->whereNull('details_sync')
            ->pluck('sku', 'id');
            //dump(count($genbaProducts));
            //dd($genbaProducts);
            foreach ($genbaProducts as $key => $genbaProduct) {
                $genresArr = [];
                //$genbaProduct = '982c9cf8-42a1-43b2-8731-b64a32b50545';
                $rowData = GenbaFunctionsHelper::getRequestResult('get', config('constants.genba.products') . '/' . $genbaProduct);
                //print_r($rowData);
                if ($rowData) {
                    $data = json_decode($rowData);
                    //dump($data);

                    $publisher = GenbaPublishers::where('name', $data->Publisher)->first();
                    if (!$publisher) {
                        $publisher = new GenbaPublishers();
                        $publisher->name = $data->Publisher;
                        $publisher->lable = $data->PublisherLabel;
                        $publisher->save();
                    }
                    $developer = GenbaDevelopers::where('name', $data->Developer)
                        ->where('protectionSystem', $data->ProtectionSystem)
                        ->first();
                    if (!$developer) {
                        $developer = new GenbaDevelopers();
                        $developer->name = $data->Developer;
                        $developer->protectionSystem = $data->ProtectionSystem;
                        $developer->save();
                    }
                    $platform = Platforms::where('name', $data->Platform)->first();
                    if (!$platform) {
                        $platform = new Platforms();
                        $platform->name = $data->Platform;
                        $platform->save();
                    }
                    if (count($data->Genres) > 0) {
                        foreach ($data->Genres as $genres) {
                            $genre = Genres::where('name', $genres)->first();
                            if (!$genre) {
                                $genre = new Genres();
                                $genre->name = $genres;
                                $genre->save();
                            }
                            array_push($genresArr, $genre->id);
                        }
                    }

                    $productDetails = GenbaProductDetails::where('product_id', $key)->first();
                    if (!$productDetails) {
                        $productDetails = new GenbaProductDetails();
                    }
                    $productDetails->product_id = $key;
                    $productDetails->releaseDate = $data->ReleaseDate;
                    $productDetails->digitalReleaseDate = $data->DigitalReleaseDate;
                    $productDetails->platform_id = $platform->id;
                    $productDetails->publisher_id = $publisher->id;
                    $productDetails->developer_id = $developer->id;
                    $productDetails->preLiveState = (isset($data->PreLiveState)) ? $data->PreLiveState : 2;
                    $productDetails->IsBundle = (isset($data->IsBundle)) ? $data->IsBundle : false;
                    $productDetails->genres = json_encode($genresArr);
                    $productDetails->keyProvider = ($data->KeyProvider) ? $data->KeyProvider->Name : '';
                    $productDetails->save();

                    if ($data->KeyProvider->Instructions) {
                        foreach ($data->KeyProvider->Instructions as $instructionData) {
                            if (!in_array($instructionData->Language, $acceptableLanguages)) {
                                continue;
                            }
                            $instruction = GenbaInstructions::where('product_id', $key)
                                ->where('language', $instructionData->Language)
                                ->first();
                            if (!$instruction) {
                                $instruction = new GenbaInstructions();
                            }
                            $instruction->product_id = $key;
                            $instruction->language = $instructionData->Language;
                            $instruction->value = $instructionData->Value;
                            $instruction->save();
                        }
                    }
                    if ($data->MetaData && count($data->MetaData) > 0) {
                        foreach ($data->MetaData as $rowData) {
                            $tempData = GenbaMetaData::where('product_id', $key)
                                ->where('parentCategory', $rowData->ParentCategory)
                                ->first();
                            if (!$tempData) {
                                $tempData = new GenbaMetaData();
                            }
                            $tempData->product_id = $key;
                            $tempData->parentCategory = $rowData->ParentCategory;
                            $tempData->values = json_encode($rowData->Values);
                            $tempData->save();
                        }
                    }

                    if ($data->Languages && count($data->Languages) > 0) {
                        foreach ($data->Languages as $rowData) {
                            if (!in_array($rowData->LanguageName, $acceptableLanguages)) {
                                continue;
                            }
                            $tempData = GenbaLanguages::where('product_id', $key)
                                ->where('languageName', $rowData->LanguageName)
                                ->first();
                            if (!$tempData) {
                                $tempData = new GenbaLanguages();
                            }
                            $tempData->product_id = $key;
                            $tempData->languageName = $rowData->LanguageName;
                            $tempData->localisedName = $rowData->LocalisedName;
                            $tempData->localisedDescription = (isset($rowData->LocalisedDescription)) ? $rowData->LocalisedDescription : '';
                            $tempData->localisedKeyFeatures = ($rowData->LanguageName) ? $rowData->LanguageName : '';
                            $tempData->legalText = ($rowData->LanguageName) ? $rowData->LanguageName : '';

                            $tempData->save();
                        }
                    }
                    if ($data->Graphics && count($data->Graphics) > 0) {
                        foreach ($data->Graphics as $rowData) {
                            $tempData = GenbaGraphics::where('product_id', $key)
                                ->where('graphic_id', $rowData->Id)
                                ->first();
                            if (!$tempData) {
                                $tempData = new GenbaGraphics();
                            }
                            $tempData->product_id = $key;
                            $tempData->graphic_id = $rowData->Id;
                            $tempData->graphicType = $rowData->GraphicType;
                            $tempData->fileSize = $rowData->FileSize;
                            $tempData->fileName = $rowData->FileName;
                            $tempData->imageUrl = $rowData->ImageUrl;
                            $tempData->originalWidth = $rowData->OriginalWidth;
                            $tempData->OriginalHeight = $rowData->OriginalHeight;


                            $tempData->save();
                        }
                    }
                    if ($data->VideoURLs && count($data->VideoURLs) > 0) {
                        foreach ($data->VideoURLs as $rowData) {
                            $tempData = GenbaVideoUrls::where('product_id', $key)
                                ->where('video_id', $rowData->ID)
                                ->first();
                            if (!$tempData) {
                                $tempData = new GenbaVideoUrls();
                            }
                            $tempData->product_id = $key;
                            $tempData->video_id = $rowData->ID;
                            $tempData->video_url = $rowData->URL;
                            $tempData->posterFrameURL = $rowData->PosterFrameURL;
                            $tempData->language = $rowData->Language;
                            $tempData->provider = $rowData->Provider;

                            $tempData->save();
                        }
                    }
                    if ($data->LocalisationSet) {
                        $genbaGameLanguages=new GenbaGameLanguages();
                        $genbaGameLanguages->product_id=$key;
                        $genbaGameLanguages->spokenLanguageSet=json_encode($data->LocalisationSet->SpokenLanguageSet);
                        $genbaGameLanguages->subtitleLanguageSet=json_encode($data->LocalisationSet->SubtitleLanguageSet);
                        $genbaGameLanguages->menuLanguageSet=json_encode($data->LocalisationSet->MenuLanguageSet);
                        $genbaGameLanguages->save();
                    }

                    if ($data->Restrictions && isset($data->Restrictions->WhitelistCountryCodes)) {
                        $restrictions = GenbaRestrictions::where('product_id', $key)
                            ->first();
                        if (!$restrictions) {
                            $restrictions =  new GenbaRestrictions();
                        }
                        $restrictions->product_id = $key;
                        $restrictions->whitelistCountryCodes = json_encode($data->Restrictions->WhitelistCountryCodes);
                        $restrictions->save();
                    }
                }
                $genbaProductDateSync = GenbaProducts::where('id', $key)->first();
                $genbaProductDateSync->details_sync = Carbon::now();
                $genbaProductDateSync->save();

                $genbaAgeRestrictions= new GenbaAgeRestrictions();
                $genbaAgeRestrictions->product_id=$key;
                $genbaAgeRestrictions->age=16;
                $genbaAgeRestrictions->save();
            }
        } catch (Exception $ex) {
            dd($ex->getMessage());
        }



        //GenbaDevelopers()
        //dd(json_decode($rowData));
    }
}
