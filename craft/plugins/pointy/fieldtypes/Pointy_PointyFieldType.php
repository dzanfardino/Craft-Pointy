<?php

namespace Craft;

use Imagine\Image\Point;

class Pointy_PointyFieldType extends BaseFieldType
{
    public function getName()
    {
        return Craft::t('Pointy');
    }

    public function defineContentAttribute()
    {
        return AttributeType::Mixed;
    }

    public function getInputHtml($name, $value)
    {
        $assetId = null;
        $assetUrl = null;


        // create our $value array from x|y string
        if ($value && is_string($value)) {

            PointyPlugin::log('Value is a string - ' . $value);
            $value = explode('|', $value);
            //[$assetId, $x, $y] = $this->parseValue($value);
            $x = (isset($value[0])) ? $value[0] : 0;
            $y = (isset($value[1])) ? $value[1] : 0;
        }
        // $value comes back as an array if it's a validation error
        // like the title is missing for example
        else {
            //PointyPlugin::log($value['coordinates']);
            $assetId = $value['assetId'] ?: null;
            if ($assetId) {
                // get assetUrl
                $file = craft()->assets->getFileById($assetId);
                $assetUrl = $file->getUrl();
            }
            $encryptionKey = craft()->config->get('encryptionKey', 'pointy');
            $c = base64_decode($value['coordinates']);
            $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
            $iv = substr($c, 0, $ivlen);
            $ciphertext_raw = substr($c, $ivlen/*+$sha2len*/);
            $coordinates = openssl_decrypt($ciphertext_raw, $cipher, $encryptionKey, $options=OPENSSL_RAW_DATA, $iv);
            $coordinates = explode('|', $coordinates);
            $x = (isset($coordinates[0])) ? $coordinates[0] : 0;
            $y = (isset($coordinates[1])) ? $coordinates[1] : 0;
        }


        return craft()->templates->render(
            'pointy/input',
            [
                'uploadFolderId' => $this->determineUploadFolderId($this->getSettings()),
                'settings'       => $this->getSettings(),
                'name'           => $name,
                'x'              => $x,
                'y'              => $y,
                'assetId'        => $assetId,
                'assetUrl'       => $assetUrl,
                'rawValue' => $coordinates,
            ]
        );
    }

//    public function prepValue($value)
//    {
//        // @todo: decrypt value
//        if ($value) {
//            $value = explode('|', $value);
//        }
//
//        $x = (isset($value[0])) ? $value[0] : '';
//        $y = (isset($value[1])) ? $value[1] : '';
//        $assetId = null;
//        $assetUrl = null;
//
//        return compact('x', 'y', 'assetId', 'assetUrl');
//    }

    /**
     * @param $value
     * @return string
     */
    public function prepValueFromPost($value)
    {
        try {
            $encryptionKey = craft()->config->get('encryptionKey', 'pointy');

            //if (is_array($value)) {
            $coordinates = $value['x'] . '|' . $value['y'];

            //$key previously generated safely, ie: openssl_random_pseudo_bytes
            $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
            $iv = openssl_random_pseudo_bytes($ivlen);
            $ciphertext_raw = openssl_encrypt($coordinates, $cipher, $encryptionKey, $options = OPENSSL_RAW_DATA, $iv);
            $encryptedCoords = base64_encode($iv . $ciphertext_raw);

            $valuesToStore = [
                'coordinates' => $encryptedCoords,
                'assetId'     => $value['assetId'],
            ];
            return $valuesToStore;
        } catch (\Exception $e) {
            PointyPlugin::log(__METHOD__ . ' : ' . $e->getMessage(), LogLevel::Error);
        }
    }

    protected function defineSettings()
    {
        return [
            'imageTypes'           => [
                AttributeType::Mixed,
                'default' => [
                    'Fixed',
                    'Dynamic',
                ],
            ],
            'imageType'            => [AttributeType::Mixed],
            'fixedImageUrl'        => [
                AttributeType::Mixed,
                'default' => 'http://maps.googleapis.com/maps/api/staticmap?center=Brooklyn+Bridge,New+York,NY&zoom=13&size=600x300&maptype=roadmap&sensor=false',
            ],
            'uploadLocationSource' => AttributeType::Number,
        ];
    }

    public function getSettingsHtml()
    {
        foreach (craft()->assetSources->getAllSources() as $source) {
            //$sourceOptions[] = $source;
            $sourceOptions[] = ['label' => $source->name, 'value' => $source->id];
        }
        return craft()->templates->render('pointy/settings', [
            'settings'      => $this->getSettings(),
            'pointyJs'      => UrlHelper::getResourceUrl('resources/pointy.js'),
            'sourceOptions' => $sourceOptions,
        ]);
    }

    /**
     * Determine an upload folder id by looking at the settings and whether Element this field belongs to is new or not.
     *
     * @param BaseModel $settings
     * @param bool $createDynamicFolders whether missing folders should be created in the process
     *
     * @throws Exception
     * @return mixed|null
     */
    private function determineUploadFolderId($settings)
    {

        $folderSourceId = $settings->uploadLocationSource;

        $folderId = $this->resolveSourcePathToFolderId($folderSourceId);


        return $folderId;
    }

    private function resolveSourcePathToFolderId($sourceId)
    {
        // Get the root folder in the source
        $rootFolder = craft()->assets->getRootFolderBySourceId($sourceId);

        // Make sure the root folder actually exists
        if (!$rootFolder) {
            throw new InvalidSourceException();
        }

        $folder = $rootFolder;

        return $folder->id;
    }

    private function parseValue($value)
    {
        // unserialize array
        // decrypt coordinates values
        $values = json_decode($value);

    }
}