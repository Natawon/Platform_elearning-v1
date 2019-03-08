<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\AdminsGroups;
use App\Models\Admins;

use Auth;

use File;
use Crypt;
use Carbon\Carbon;
use phpseclib\Crypt\RSA;
use phpseclib\Crypt\AES;
use phpseclib\Crypt\Random;
use phpseclib\Math\BigInteger;


class _SecurityController extends Controller
{
    // public $xxx;
    // protected $xxx;
    // private $xxx;

    public function __construct()
    {
        date_default_timezone_set('Asia/Bangkok');
        config(['app.timezone' => 'Asia/Bangkok']);
    }

    public function csrfToken()
    {
        /*return csrf_token();*/
        return response('', 200);
    }

    public function csrfTokenSite()
    {
        // return response()->json(["csrf_token" => csrf_token()], 200);
        $csrf_token = csrf_token();
        return response()->json(["csrf_token" => $csrf_token, "encrypted_csrf_token" => Crypt::encrypt($csrf_token)], 200);
    }

    public function generateKeyFiles($rsaKeySize = 2048, $aesKeySize = 128)
    {
        $rsa = new RSA();
        $cipher = new AES(AES::MODE_ECB);

        try {

            $rsa->setPrivateKeyFormat($rsa::PRIVATE_FORMAT_XML);
            $rsa->setPublicKeyFormat($rsa::PUBLIC_FORMAT_XML);

            $xmlRsaKeys = $rsa->createKey((int)$rsaKeySize);

            $cipher->setKeyLength((int)$aesKeySize);
            // $cipher->setKey('setelearning');

            $aesKey = base64_encode($cipher->encrypt(''));
            $aesKeyValue = "<AESKeyValue>".$aesKey."</AESKeyValue>";

            $privateKeyFile = $xmlRsaKeys['privatekey']."\r\n".$aesKeyValue;
            $publicKeyFile = $xmlRsaKeys['publickey']."\r\n".$aesKeyValue;

            $privateBytesWritten = File::put(env('PATH_PRIVATE_KEY'), $privateKeyFile);

            if ($privateBytesWritten === false) {
                return false;
            }

            $publicBytesWritten = File::put(env('PATH_PUBLIC_KEY'), $publicKeyFile);

            if ($publicBytesWritten === false) {
                return false;
            }

            return true;

        } catch (Exception $e) {
            // echo 'Caught exception: ',  $e->getMessage(), "\n";
            return false;
        }
    }

    public function encryptAndSignData($data, $expired = 15)
    {
        $results = array(
            "isSuccess" => true,
            "data" => null,
            "signature" => null,
            "message" => null
        );

        $carbon = Carbon::now('Asia/Bangkok');

        if (!isset($expired) || !is_numeric($expired)) {
            $expired = $carbon->addDays(15)->toDateTimeString();
        } else {
            $expired = $carbon->addDays($expired)->toDateTimeString();
        }

        $data = $data."|".$expired;

        $privateKeysXml = File::get(env('PATH_PRIVATE_KEY'));
        $dataEncrypted = $this->aesEncryptString($data, $this->getAesKeys($privateKeysXml));

        if ($dataEncrypted === false) {
            $results['isSuccess'] = false;
            $results['message'] = "Failed to encrypt data.";
        } else {

            $rsa = new RSA();
            $rsa->setSignatureMode($rsa::SIGNATURE_PKCS1);
            $rsa->loadKey($privateKeysXml);

            // $dataToSign = new BigInteger(mb_convert_encoding($dataEncrypted, 'UTF-16LE'));
            // $dataToSign = new BigInteger(bin2hex(mb_convert_encoding($dataEncrypted, 'UTF-16LE')));
            // $results['data'] = $dataToSign->toBytes();
            // $results['data']['Data Encrypted'] = $dataEncrypted;
            // $results['data']['x2'] = mb_internal_encoding();
            // $results['data']['Data Conver UTF-16LE'] = mb_convert_encoding($dataEncrypted, 'UTF-16LE');
            // $results['data']['x4'] = mb_strlen(mb_convert_encoding($dataEncrypted, 'UTF-16LE'));
            // $dataToSign = new BigInteger(mb_strlen(mb_convert_encoding($dataEncrypted, 'UTF-16LE')), 16);
            // $results['data']['x5'] = $dataToSign->toBytes();
            // $results['data']['x6'] = mb_strlen($dataEncrypted, 'UTF-16LE');
            // $results['data']['x7'] = iconv_strlen($dataEncrypted, 'UTF-16LE');
            // $dataToSign2 = new BigInteger(mb_strlen($dataEncrypted, 'UTF-16LE'), 16);
            // $results['data']['x8'] = $dataToSign2->toBytes();


            // $dataToSign = new BigInteger(pack('H*', bin2hex(mb_convert_encoding($dataEncrypted, 'UTF-16LE'))), 256);
            $dataBytes = unpack('C*', mb_convert_encoding($dataEncrypted, 'UTF-16LE'));
            $dataToSign = call_user_func_array("pack", array_merge(array("C*"), $dataBytes));
            // $results['data']['(toBytes)'] = ($dataToSign->toBytes());
            // $results['data']['bin2hex(dataConvert)'] = bin2hex(mb_convert_encoding($dataEncrypted, 'UTF-16LE'));
            // $results['data']['x9-1'] = pack('C*', (mb_convert_encoding($dataEncrypted, 'UTF-16LE')));

            $rsa->setHash('sha512');
            // $rsa->setSignatureMode($rsa::SIGNATURE_PKCS1);
            $signature = $rsa->sign($dataToSign);
            // $results['data']['dataToSign'] = ($dataToSign);
            // $results['data']['Signature'] = base64_encode($signature);
            $results['data'] = $dataEncrypted;
            $results['signature'] = base64_encode($signature);

            // $publicKeysXml = File::get(env('PATH_PUBLIC_KEY'));
            // $rsa->loadKey($publicKeysXml);
            // $results['data_'] = $rsa->verify(($dataToSign->toBytes()), $signature);

        }

        return $results;
    }

    public function verifyData($data = null, $signature = null , $pathPublicKeys)
    {
        $results = array(
            "isSuccess" => false,
            "trusted" => false,
            "expired" => null,
            "message" => null,
            "data" => null
        );

        try {

            /* Check Input */
            if (!isset($data) && !isset($signature)) {
                $results['message'] = "E004: Input parameter (data, signature) is not valid.";
                return $results;
            } else if (!isset($data)) {
                $results['message'] = "E004: Input parameter (data) is not valid.";
                return $results;
            } else if (!isset($signature)) {
                $results['message'] = "E004: Input parameter (signature) is not valid.";
                return $results;
            }

            $publicKeysXml = File::get($pathPublicKeys);

            $rsa = new RSA();
            $rsa->setSignatureMode($rsa::SIGNATURE_PKCS1);
            $rsa->loadKey($publicKeysXml);
            $rsa->setHash('sha512');

            // $data = "abc";

            // $data = mb_convert_encoding($data, 'UTF-16LE');

            // $dataToVerify = new BigInteger(pack('H*', bin2hex(mb_convert_encoding($data, 'UTF-16LE'))), 256);
            // $dataToVerify = new BigInteger(unpack('C*', mb_convert_encoding($data, 'UTF-16LE')), 256);
            // $dataToVerify = new BigInteger(pack('H*', bin2hex($data)), 256);
            // var_dump($dataToVerify);
            // var_dump($dataToVerify->value);
            // var_dump($dataToVerify->toString());
            // var_dump($dataToVerify->toBytes());
            // var_dump($dataToVerify->__toString());
            // $data = base64_decode($data);
            // $data = $this->aesDecryptString($data, $this->getAesKeys($publicKeysXml));

            // var_dump($data);
            // $data = $this->aesDecryptString($data, $this->getAesKeys($publicKeysXml));
            // $dataToVerify_ = unpack('C*', mb_convert_encoding($data, 'UTF-16LE'));
            // $str = call_user_func_array("pack", array_merge(array("C*"), $dataToVerify_));
            // $str = call_user_func_array("pack", array_merge(array("C*"), $dataToVerify));
            // $str = (implode(array_map("chr", $dataToVerify_)));
            // $dataToVerify = new BigInteger(pack('H*', bin2hex($data)), 256);
            // $results['b1'] = $dataToVerify;
            // return $results;

            // $dataToVerify = new BigInteger(pack('H*', bin2hex($data)), 256);
            // // $results['b2'] = bin2hex($dataToVerify->toBytes());
            // $results['b3'] = $dataToVerify;
            // $results['b31'] = $dataToVerify->toBytes();

            // // $results['x'] = $data;
            // // $results['y'] = mb_convert_encoding($data, 'UTF-16LE');
            // // $results['z'] = pack('H*', bin2hex($data));
            // return $results;

            // var_dump($dataToVerify);
            // echo "string";
            // $decodeToVerify = $dataToVerify->toString();

            $dataBytes = unpack('C*', mb_convert_encoding($data, 'UTF-16LE'));
            $dataToVerify = call_user_func_array("pack", array_merge(array("C*"), $dataBytes));

            $signatureBytes = base64_decode($signature);
            // $signatureToVerify = new BigInteger($signatureBytes, 256);
            // $decodeSignatureBytes = $signatureToVerify->toString();


            // $results['data'] = $plainText;
            // $results['signature'] = $signature;
            // return $results;
            // $rsa->setSignatureMode($rsa::SIGNATURE_PKCS1);

            if (!$rsa->verify($dataToVerify, $signatureBytes)) {
                $results['message'] = "E001: Signature is not valid.";
                return $results;
            }

            $plainText = $this->aesDecryptString($data, $this->getAesKeys($publicKeysXml));

            if ($plainText === false || $plainText == "") {
                $results['message'] = "E002: Decryption do not success.";
                return $results;
            }

            $splitData = preg_split("/\|(?=(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})*$)/", $plainText);

            if ($splitData === false || count($splitData) != 2) {
                $results['message'] = "E002: Decryption do not success.";
                return $results;
            }

            $dataObjJson = @json_decode($splitData[0], true);

            if ($dataObjJson === null && json_last_error() !== JSON_ERROR_NONE) {
                $results['data'] = $splitData[0];
                $results['isJson'] = false;
                // $results['data_debug'] = json_last_error();
            } else {
                $results['data'] = $dataObjJson;
                $results['isJson'] = true;
            }

            $dateNow = Carbon::now();
            $dateExpired = Carbon::parse($splitData[1]);

            if ($dateNow->gt($dateExpired)) {
                $results['message'] = "E003: Request expired.";
                // $results['debug_expired'] = $dateExpired;
                // $results['debug_now'] = $dateNow;
                return $results;
            }

            $results['isSuccess'] = true;
            $results['trusted'] = true;
            $results['expired'] = $dateExpired->toDateTimeString();


            // $results['debug'] = $plainText;

            // $testArr = ["��Ե��", "�Ը��Ժ����"];

            // mb_convert_variables('UTF-16', 'UTF-16', $testArr);
            // $debug = mb_convert_encoding("��Ե��", 'UTF-16LE');
            // mb_convert_variables('UTF-8', 'UTF-16', $testArr);
            // $results['debug'] = mb_convert_encoding($splitData[0], 'UTF-16LE', 'UTF-8');
            // $results['debug'] = @iconv( 'UTF-8//IGNORE', 'UTF-8', $splitData[0]);
            // $results['debug'] = $splitData[0];
            // $results['debug_1'] = iconv('UTF-8', 'UTF-8//IGNORE', $splitData[0]);
            // $results['debug_2'] = iconv('UTF-8', 'UTF-8//TRANSLIT', $splitData[0]);

            // $results['debug'] = mb_convert_encoding($splitData[0], "UTF-8", mb_detect_encoding($splitData[0]));

            // var_dump($results);

            // return $results;

            // $splitData[0] = mb_convert_encoding($splitData[0], 'UTF-8');
            // $dataObjJson = @json_decode($splitData[0], true);

            // if ($dataObjJson === null && json_last_error() !== JSON_ERROR_NONE) {
            //     $results['data'] = $splitData[0];
            //     $results['isJson'] = false;
            //     // $results['data_debug'] = json_last_error();
            // } else {
            //     $results['data'] = $dataObjJson;
            //     $results['isJson'] = true;
            // }

            return $results;

        } catch (Exception $e) {
            $results['message'] = "Caught exception: ".$e->getMessage();
        }

        return $results;
    }

    public function getAesKeys($key, $format = 'xml')
    {
        // $oFns = new _FunctionsController;

        $result = "";

        // preg_match("/^<RSAKeyValue>(.*)<\/RSAKeyValue>/", $privateKeysXml, $output_array);
        // $output_array = preg_split("/^<RSAKeyValue>(.*)<\/RSAKeyValue>/", $privateKeysXml);
        // preg_match_all("/^<RSAKeyValue>(.*)<\/RSAKeyValue>/s", $privateKeysXml, $output_array);
        // preg_match_all("/<RSAKeyValue>(.*)<\/RSAKeyValue>/s", $privateKeysXml, $output_array);

        // $arr = $oFns->namespacedXMLToArray($privateKeysXml);
        switch ($format) {
            // case 'xxx':
            //     # code...
            //     break;

            default:

                if (preg_match("/<AESKeyValue>(.*)<\/AESKeyValue>/", $key, $output_array)) {
                    $result = $output_array[1];
                }

                break;
        }

        return $result;
    }

    public function aesEncryptString($plainText, $key)
    {
        $result = "";

        try {

            if (!isset($plainText) || $plainText == "") {
                return false;
            }

            $aesKeyBytes = base64_decode($key);

            $cipher = new aes(AES::MODE_ECB);
            $cipher->setKey($aesKeyBytes);
            $result = base64_encode($cipher->encrypt($plainText));

        } catch (Exception $e) {
            return false;
        }

        return $result;
    }

    public function aesDecryptString($cipherText, $key)
    {
        $result = "";

        try {

            if (!isset($cipherText) || $cipherText == "") {
                return false;
            }

            $cipherTextBytes = base64_decode($cipherText);
            $aesKeyBytes = base64_decode($key);

            $cipher = new aes(AES::MODE_ECB);
            $cipher->setKey($aesKeyBytes);
            $result = $cipher->decrypt($cipherTextBytes);

        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $result;
    }

}
