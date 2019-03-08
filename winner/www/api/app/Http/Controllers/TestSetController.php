<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use GuzzleHttp\Client as httpClient;
use GuzzleHttp\Exception\GuzzleException;

use App\Models\Categories;
use App\Models\Courses;
use App\Models\Groups;
use App\Models\Members;

use Torann\GeoIP\Facades\GeoIP as GeoIP;
use Carbon\Carbon;
use File;
use Auth;


class TestSetController extends Controller
{
    private function syncMember($ref_id, $data, $dataGroup, $isLogin = true)
    {
        $dataMember = Members::where('ref_id', $ref_id)->first();
        $dataGeoIP = GeoIP::getLocation();
        $agent = new Agent();

        if (!$dataMember) {
            $dataMember = new Members;
            $dataMember->fill($data['user_profile']);
            $dataMember->groups_id = $dataGroup->id;
            $dataMember->company_code = isset($data['company_code']) ? $data['company_code'] : null;
            $dataMember->ip = $dataGeoIP['ip'];
            $dataMember->country = $dataGeoIP['country'];
            $dataMember->city = $dataGeoIP['city'];
            $dataMember->device = $agent->device();
            $dataMember->platform = $agent->platform();
            $dataMember->platform_version = $agent->version($dataMember->platform);
            $dataMember->create_datetime = date('Y-m-d H:i:s');
            $dataMember->modify_datetime = date('Y-m-d H:i:s');

            if ($isLogin) {
                $dataMember->last_login = date('Y-m-d H:i:s');
            }

            $is_success = $dataMember->save();
        } else {
            $dataMember->fill($data['user_profile']);
            $dataMember->ip = $dataGeoIP['ip'];
            $dataMember->country = $dataGeoIP['country'];
            $dataMember->city = $dataGeoIP['city'];
            $dataMember->device = $agent->device();
            $dataMember->platform = $agent->platform();
            $dataMember->platform_version = $agent->version($dataMember->platform);
            $dataMember->modify_datetime = date('Y-m-d H:i:s');

            if ($isLogin) {
                $dataMember->last_login = date('Y-m-d H:i:s');
            }

            $is_success = $dataMember->save();
        }

        return array("is_success" => $is_success, "dataMember" => $dataMember);
    }

    public function testDecrypt(_SecurityController $_security)
    {
        // $cipherText = "8OjQFvxLvebi\/mfKpjDDTuRg0gYvD+aHziMLH0K2cok=";
        $cipherText = "WyKmX\/U9ROBgkHdD5POc9g==";

        // $cipherText = "vKt+RM0/LxSjItuBoGmkVdu3RFqUsd0HzON0v/MKk2KIQI56esjvJUVCve8zh/UbaDcxhjVFeLXB4+6RWnoc0Q==";
        $cipherText = "AxSIdiMOD/NJ+yh3k2O2mjW8O/StjSFbZ9hvLgn9q/+Cat0sbzWi+pZ4xEtPkEDY";
        // $cipherText = ("vKt+RM0/LxSjItuBoGmkVUF2EAgD2U8OIXFOiFKw8awBPmjihKqMtOVFZW1zQfjw");
        // $cipherText = base64_encode("FK2m6TEg/9WBtEt4CoxEUtX37mEUWhfmTEKSKJv9oRBUqWmZYMiRgGKqls5qZEKaYVpQV+vN4COXQ8JBKZVUg68kEqnyfsXRLCXhmSaB34+xqAR2S1T/aLXeXVWV51ELVC3vAz+7ouFyvyZ1MP3flajQzIArpPXVKsqD6r0E+zKxStwAwwqCXcPI27OssCU553o9T+x7Bx1fVgWhSljyDrsu9vyHN3gCF7mwGWYNtSsNG4c38tmlGbJTgtiHrGrFcsG0/Nkh2LX6iUzfTyB67Q0r+5k1kfI5jSXuLnr7FI3ex2s6pwo0PzYhsHZwkdAM6e9s/sUSX8rpLJK1gOl+t8zxsQ++WiaDIhIXXlvKddsbkmcoFdMWV1l+wjDnj1qYKRFeenklWoC9T8JNXr6qW13D0GKImMwYJST6IFMNoS0gs1zqsA5/j+jKpP8Ssa2Pr0wh0rcqkwiv4LBpHZgrwH9xa1r4/AAuT4+qkVnwcl/K8wIpYr2rzRhC6eCEdZ4+rYm5atrYwUaAHXtjDNNtidhe/GBfsxaW7u3b+ADzW8a75k9nInRph3K35wAvQwsnttjV5kKmdpTCUbq7S4KOQ8ckR+lkX3a/oEDNdhz2LoeZdyP+jmJukizP1XakQQeTbeffnH4eFYPNXZiRJre9/716tcA40NSLTE6++O77Vvu+kKRo30USMHGwgQXZftFUjNzKRAhhS+MdjGLJa9bFsJIPYKielLxjq/danB1FzMgSoE5KVscld6PfbwbbG3ePypZ67vLAfIQ2qnIQnZdkOvA2dHaBQp9fx+xM2SxAYBmnUl8Tc8DERrEYMlSQ2HKNnmjO+ZRycAYHWf5wq2kvO+Y+bnq5KrzHaHk+r8Q8OAQ49gS97YsKYWtr40iWG21wgkQ5laqn+m217yZ6XmNTkQ6R5oktIWsjS5lvM1UPGeU1vIRlqURNYUo/pwfFaLEPfz67ZUVsdGxTDnAhIDmpGYi/qLbQu8lzV78rzFlAUrygaSnfPip8rSJK6Qq11x6zTcXCXorDMDjW9/594pfysKhaSywTfjGIVSfx3koW52ts1u3SSXCfQH9UFmytCfT54dWSswDvn7fQXj1x1YxxgTt6VWBZZhjIPBU1M3XX95t2Wn57N3EITDDA/2/TnvPRyWiSH5gbU+goBQXRToJITtxijnAnptFujyzz1+rFL13Q6Kl+k7HHcQKDoUx/5ElHtG5OJ4zb7o2WkaH+bgc1GuZJca6D3zFv18T544oxci9fm3C5Zsyf01H2/YCbKdrk0sVTalqOTGkpwSuMYQvOdxSUG/bZkg/N2LT0Lktz/PmZ8ZOPDmaJvVYwz7X7vjHf1Dlh9kB9ot+w0rVPtmqbyY82op89oMs6YXUqJxe5zCLcakQac5b6WdbPnsVob3lSYtHpU5VMrB6bn43WPTNCLZQXLU2eGtStUYRfwxMum9Xu3imYa15nYSIp7CtExpNK619QoLMjiyrBDfTKB2D1vFHIldMtxhU8L7cbTtLjr8LEmAbY0iZsfFhkWyVZKMCi+FfgvuFK+RJPlxUUnX3gpEfyAShpjkO54hZf/Gu3zgevA2tyICAPeztM2cvCUV3+P9iB1zZPbBOvc8wuzhgREZ0orx/sTEc5Gn+jdFwV5rsRv6mcjD71crQQsjCvj6eU6mBMDhyWGHAKHYfjxoRTsRusKWBvph+zSsx1bx3lbAROUyLIzhlriEb1vNhdSaGg+6Stxm0c69urukd08tZE5Qf/Z1Ngi6BTKFLMO5v8ZyXEISMxDfc7v//gvSSqMY05249HzSSOth7X7hLNV/uZlvjp3O4dEun2oUWr+b0fb//p+wtJVwqnxP+l39WJq+nps7ss2I+MJjr1skxIx42n7gZJo23Vk1nnSpORNj9suHM=");
        // $cipherText = "FK2m6TEg/9WBtEt4CoxEUtX37mEUWhfmTEKSKJv9oRBUqWmZYMiRgGKqls5qZEKaYVpQV+vN4COXQ8JBKZVUg68kEqnyfsXRLCXhmSaB34+xqAR2S1T/aLXeXVWV51ELVC3vAz+7ouFyvyZ1MP3flajQzIArpPXVKsqD6r0E+zKxStwAwwqCXcPI27OssCU553o9T+x7Bx1fVgWhSljyDrsu9vyHN3gCF7mwGWYNtSsNG4c38tmlGbJTgtiHrGrFcsG0/Nkh2LX6iUzfTyB67Q0r+5k1kfI5jSXuLnr7FI3ex2s6pwo0PzYhsHZwkdAM6e9s/sUSX8rpLJK1gOl+t8zxsQ++WiaDIhIXXlvKddsbkmcoFdMWV1l+wjDnj1qYKRFeenklWoC9T8JNXr6qW13D0GKImMwYJST6IFMNoS0gs1zqsA5/j+jKpP8Ssa2Pr0wh0rcqkwiv4LBpHZgrwH9xa1r4/AAuT4+qkVnwcl/K8wIpYr2rzRhC6eCEdZ4+rYm5atrYwUaAHXtjDNNtidhe/GBfsxaW7u3b+ADzW8a75k9nInRph3K35wAvQwsnttjV5kKmdpTCUbq7S4KOQ8ckR+lkX3a/oEDNdhz2LoeZdyP+jmJukizP1XakQQeTbeffnH4eFYPNXZiRJre9/716tcA40NSLTE6++O77Vvu+kKRo30USMHGwgQXZftFUjNzKRAhhS+MdjGLJa9bFsJIPYKielLxjq/danB1FzMgSoE5KVscld6PfbwbbG3ePypZ67vLAfIQ2qnIQnZdkOvA2dHaBQp9fx+xM2SxAYBmnUl8Tc8DERrEYMlSQ2HKNnmjO+ZRycAYHWf5wq2kvO+Y+bnq5KrzHaHk+r8Q8OAQ49gS97YsKYWtr40iWG21wgkQ5laqn+m217yZ6XmNTkQ6R5oktIWsjS5lvM1UPGeU1vIRlqURNYUo/pwfFaLEPfz67ZUVsdGxTDnAhIDmpGYi/qLbQu8lzV78rzFlAUrygaSnfPip8rSJK6Qq11x6zTcXCXorDMDjW9/594pfysKhaSywTfjGIVSfx3koW52ts1u3SSXCfQH9UFmytCfT54dWSswDvn7fQXj1x1YxxgTt6VWBZZhjIPBU1M3XX95t2Wn57N3EITDDA/2/TnvPRyWiSH5gbU+goBQXRToJITtxijnAnptFujyzz1+rFL13Q6Kl+k7HHcQKDoUx/5ElHtG5OJ4zb7o2WkaH+bgc1GuZJca6D3zFv18T544oxci9fm3C5Zsyf01H2/YCbKdrk0sVTalqOTGkpwSuMYQvOdxSUG/bZkg/N2LT0Lktz/PmZ8ZOPDmaJvVYwz7X7vjHf1Dlh9kB9ot+w0rVPtmqbyY82op89oMs6YXUqJxe5zCLcakQac5b6WdbPnsVob3lSYtHpU5VMrB6bn43WPTNCLZQXLU2eGtStUYRfwxMum9Xu3imYa15nYSIp7CtExpNK619QoLMjiyrBDfTKB2D1vFHIldMtxhU8L7cbTtLjr8LEmAbY0iZsfFhkWyVZKMCi+FfgvuFK+RJPlxUUnX3gpEfyAShpjkO54hZf/Gu3zgevA2tyICAPeztM2cvCUV3+P9iB1zZPbBOvc8wuzhgREZ0orx/sTEc5Gn+jdFwV5rsRv6mcjD71crQQsjCvj6eU6mBMDhyWGHAKHYfjxoRTsRusKWBvph+zSsx1bx3lbAROUyLIzhlriEb1vNhdSaGg+6Stxm0c69urukd08tZE5Qf/Z1Ngi6BTKFLMO5v8ZyXEISMxDfc7v//gvSSqMY05249HzSSOth7X7hLNV/uZlvjp3O4dEun2oUWr+b0fb//p+wtJVwqnxP+l39WJq+nps7ss2I+MJjr1skxIx42n7gZJo23Vk1nnSpORNj9suHM";
        // $cipherText = "VDjbmzMRUEsdna1jC9Mzpq7BH9o9SDAa952HZR239BK4+xJlGSfvhbxXphSmsIAh2kMvg4J2RZ03grwT4NMX5Xe6pyxqp6xD43I3TqbgGSDzOLTg6X8RC/ZQgleizOZijdNgu7NkeYihrgsaUlB4Y5Yd2BXfymuha3+34nfbnFkuaCqOKA+bSGKqILaP0UneTg5Hk5LVDwBvdoJJN9h5aRotehNcaBFfDVuoLQ3B7OnZlu+xy0UZVm0kPevpTxPBBk9ffqYK8xRCd/qfdTi795MxMapIZJIkvInVVN8wcFJro7RDZIb0XQjbT015iLFY9DCAQcycUI3I3tO5B4pLnSWBbrfyZfJbEIz/VliO+wo/uoVEoAcibMU2Nmdl3Hoqf95/JGP6n8jcUpKREm+Tn36W86XseSmiP18TMMSYdcP8UirQrR0SdiHIGMC5k6ZxvtDwyEEdZfAGjhCuIwLEdwXpF83YOrP5yBpAXUVECS5bNdT9HaE8d4Tc67ucJOkgECd9/ARcrSt9czDchm9pQmNTfAXMq3hlzoYOpKQJwUOdsWQq4yjzkIQtFVbubKSJOwcQW9Q9EhRZOhqcO5QtNfdelwv4dK5f81huabcPCV7XHX1j9crXPjFZ2dYpEFNDa1eAEYwEJvg9h5yrRK+eCPeNgnbaCYrzhOO2c/FOLb89sMtjggIGskfbcLGKD43aGYnrLZp28SaWjhxVHRHO3eaFLMua7rKm+wPU1F5afHkI+wxvB2FfOqeFiSPQQ2RqBXHbvcfKZ5ewhJCoYVSdrMPw61FDn31xcab/ntdWnMs0SfSSUkDpJJEqneFPAy2KixbVe6RnZp+Dd8notFOmRsLBCxkiS8BbQjnhotx1tgIuTx+isEPm6hmMN6x4d6I4yIuW1hhHt17Zk/Zuwa6SYTIlueewevaGJY2aolQZ8OuhY+/r67m55/uY/HFzJ+FjxA5zkudNWcKwXKyYLgAthAg/HCw/xoC9RZE9KoZhOXM0iGWy46DfI65lJFjRQXTVIitp9fymNwyC05Bv0JplDnTyWEOra2bWffAYkIY2aMH/EzG5sV2ofZSTYZe66uYsunKKdbZxMFQ9YETREw3MajmDBEohNzjErzpyJkHf3cC11GoFM7t1q1kg1Oliuw9980odoHvXiAfW2XT0i3v5udI/X08P0+x1Gd1fUOYIzMjAgVW2b+mKhpdC5duW5GTZRX3fyr1lokAdfVlKoSvfK6/9jWbLAmYi+0WCNxe8XuNo140SNdw4noJrkpweKec3xUA+wVTMe1B+WfLftEdxAi/Vfn0aKBxUmmjzJ3+u2hSFJEakf5GkZusKMCEM6odjlLbrZsiABDeSDgEWCBPrnBi472kdwWiGbymmENWVjq4uB2DIvjfxBMu16dYRa/pjIObphKvHxTGucK4Adoj8tbXfPKX11k5yIODyCdu/Obs6LDvf3+HvcXbbJhp6h48oLrPgYI8SpQ2ZeuAixLoHvzRrjwv4Rpuw2Vv0KoezPMaEO3XlNQuxjZYdjcsxINthJ+nCvpygQg3breeNTfF3CNqXGwpKilJ0MuFEKsTSAXXe2lE7TlXWhDwMivAbf/BGiSZKvizFqIRexiZ9ghc6tOkVLLnpHHbcxP206ONXubxm5fa2w9s5irLrppanLz+qbTBZi3yNWdZNgwSMHB24SXuq7hqjPdR7Aqaq3NnCOoS2Ub7dLjdo3B+qGJ5EyCXAJLe8ubseClIMvcz9f2x0jfaiYNSSbpNmUzkauhti8/37gd3nvWFuXcfbBwtx3UdA0JuBcs7vF3tM4fMNx6P5MZYYRsBdoyLmKqrVkeKhfHKhvbI+G6LMrFC5kXScX+iZI/clD78TiXXsAknfWx00IZyhaf3VBxT7d1PpyPcwRXA=";
        // $publicKey = env('PATH_PUBLIC_KEY');
        $publicKey = env('PATH_SET_WEB_PUBLIC_KEY');

        $publicKeysXml = File::get($publicKey);

        $result = $_security->aesDecryptString($cipherText, $_security->getAesKeys($publicKeysXml));

        return response(array($result), 200);
        // return response($result, 200);
    }

    public function testEncrypt(_SecurityController $_security)
    {
        $plaintext = "Tes Encrypted";

        $privateKey = env('PATH_PRIVATE_KEY');
        $privateKeysXml = File::get($privateKey);

        $result = $_security->aesEncryptString($plaintext, $_security->getAesKeys($privateKeysXml));

        return response(array($result), 200);
        // return response($result, 200);
    }

    public function generateKeys(_SecurityController $_security)
    {
        $isGenerated = $_security->generateKeyFiles();

        if (!$isGenerated) {
            return response()->json(["isGenerated" => $isGenerated], 500);
        }

        return response()->json(["isGenerated" => $isGenerated], 200);
    }

    public function singleSignOn(Request $request, _SecurityController $_security)
    {
        $results = $_security->verifyData($request->data, $request->signature, env('PATH_SET_WEB_PUBLIC_KEY'));

        if ($request->server('HTTP_REFERER') !== null) {
            $referer = $request->server('HTTP_REFERER');
        } else {
            $referer = config('constants.URL.HOME');
        }

        if (!$results['isSuccess']) {
            return redirect($referer);
        }

        $dataGroup = Groups::where('key', $results['data']['group_id'])->first();

        if (!$dataGroup) {
            return redirect($referer);
        }

        if (isset($results['data']['user_profile'])) {
            $dataMember = Members::where('ref_id', $results['data']['user_profile']['ref_id'])->first();
            $dataGeoIP = GeoIP::getLocation();
            $agent = new Agent();

            if (!$dataMember) {
                $dataMember = new Members;
                $dataMember->fill($results['data']['user_profile']);
                $dataMember->groups_id = $dataGroup->id;
                $dataMember->company_code = isset($results['data']['company_code']) ? $results['data']['company_code'] : null;
                $dataMember->ip = $dataGeoIP['ip'];
                // $dataMember->my_session_id = session()->getId();
                $dataMember->country = $dataGeoIP['country'];
                $dataMember->city = $dataGeoIP['city'];
                $dataMember->device = $agent->device();
                $dataMember->platform = $agent->platform();
                $dataMember->platform_version = $agent->version($dataMember->platform);
                $dataMember->create_datetime = date('Y-m-d H:i:s');
                $dataMember->modify_datetime = date('Y-m-d H:i:s');
                $dataMember->last_login = date('Y-m-d H:i:s');
                $is_success = $dataMember->save();
            } else {
                $dataMember->fill($results['data']['user_profile']);
                $dataMember->ip = $dataGeoIP['ip'];
                // $dataMember->my_session_id = session()->getId();
                $dataMember->country = $dataGeoIP['country'];
                $dataMember->city = $dataGeoIP['city'];
                $dataMember->device = $agent->device();
                $dataMember->platform = $agent->platform();
                $dataMember->platform_version = $agent->version($dataMember->platform);
                $dataMember->modify_datetime = date('Y-m-d H:i:s');
                $dataMember->last_login = date('Y-m-d H:i:s');
                $is_success = $dataMember->save();
            }

            if (!$is_success) {
                return redirect($referer);
            } else {
                $request->session()->regenerate();
                $request->session()->put('_user', $dataMember);
                $this->swappingSession($dataMember);
            }
        }

        $action = strtoupper($results['data']['action']);
        if (config('constants.URL.'.$action)) {
            return redirect(config('constants.URL.'.$action));
        }

        return redirect(config('constants.URL.HOME'));
        // return redirect("https://www.google.co.th/");
    }

    public function logout(Request $request, _SecurityController $_security)
    {

        $_user = session()->get('_user');

        if (!isset($_user)) {
            // return redirect()->back();
            return redirect(config('constants.URL.HOME'));
        }

        $request->session()->forget('_user');

        $dataMember = Members::find($_user['id']);
        $dataMember->last_logout = date('Y-m-d H:i:s');
        $dataMember->save();

        $dataGroup = Groups::find($_user['groups_id']);

        $agent = new Agent();

        if ($agent->isMobile()) {
            $txtChannel = 'mobile';
        } else if ($agent->isTablet()) {
            $txtChannel = 'tablet';
        } else {
            $txtChannel = 'web';
        }

        $param = array(
            "userref" => $_user['ref_id'],
            "groupid" => $dataGroup->key,
            "txtChannel" => $txtChannel,
            "sessionId" => $_user['session_id']
        );

        $results = $_security->encryptAndSignData(json_encode($param));

        // return response()->json(["param" => $param, "results" => array("data" => $results['data'], "signature" => $results['signature'])], 200);
        // return response()->json(array("data" => $results['data'], "signature" => $results['signature'], "system" => "elearning"), 200);

        return response()->json(array(
            "signoutUrl" => config('constants._SET_URL.S2'),
            "params" => array("data" => $results['data'], "signature" => $results['signature'], "system" => "elearning")
        ), 200);

        // $oClient = new httpClient();

        // try {
        //     $_response = $oClient->request('POST', config('constants._SET_URL.S2'), [
        //         'json' => array("data" => $results['data'], "signature" => $results['signature'], "system" => "elearning"),
        //         'allow_redirects' => [
        //             'strict' => true,
        //             'referer' => true,
        //         ]
        //     ]);

        //     // return response()->json(array("data" => $results['data'], "signature" => $results['signature'], "system" => "elearning"), 200);
        //     echo $_response->getBody();
        // } catch(RequestException $e) {
        //     // if ($e->hasResponse()) {
        //     //     return response($this->namespacedXMLToArray($e->getResponse()->getBody()), $e->getResponse()->getStatusCode());
        //     // }
        //     return redirect()->back();
        // }
    }

    public function singleSignOnTest(Request $request, _SecurityController $_security)
    {
        $results = $_security->verifyData($request->data, $request->signature, env('PATH_PUBLIC_KEY'));

        if ($request->server('HTTP_REFERER') !== null) {
            $referer = $request->server('HTTP_REFERER');
        } else {
            $referer = config('constants.URL.HOME');
        }

        if (!$results['isSuccess']) {
            return redirect($referer);
        }

        $dataGroup = Groups::where('key', $results['data']['group_id'])->first();

        if (!$dataGroup) {
            return redirect($referer);
        }

        $dataMember = Members::where('ref_id', $results['data']['user_profile']['ref_id'])->first();
        $dataGeoIP = GeoIP::getLocation();
        $agent = new Agent();

        if (!$dataMember) {
            $dataMember = new Members;
            $dataMember->fill($results['data']['user_profile']);
            $dataMember->groups_id = $dataGroup->id;
            $dataMember->company_code = isset($results['data']['company_code']) ? $results['data']['company_code'] : null;
            $dataMember->ip = $dataGeoIP['ip'];
            // $dataMember->my_session_id = session()->getId();
            $dataMember->country = $dataGeoIP['country'];
            $dataMember->city = $dataGeoIP['city'];
            $dataMember->device = $agent->device();
            $dataMember->platform = $agent->platform();
            $dataMember->platform_version = $agent->version($dataMember->platform);
            $dataMember->create_datetime = date('Y-m-d H:i:s');
            $dataMember->modify_datetime = date('Y-m-d H:i:s');
            $dataMember->last_login = date('Y-m-d H:i:s');
            $is_success = $dataMember->save();
        } else {
            $dataMember->fill($results['data']['user_profile']);
            $dataMember->ip = $dataGeoIP['ip'];
            // $dataMember->my_session_id = session()->getId();
            $dataMember->country = $dataGeoIP['country'];
            $dataMember->city = $dataGeoIP['city'];
            $dataMember->device = $agent->device();
            $dataMember->platform = $agent->platform();
            $dataMember->platform_version = $agent->version($dataMember->platform);
            $dataMember->modify_datetime = date('Y-m-d H:i:s');
            $dataMember->last_login = date('Y-m-d H:i:s');
            $is_success = $dataMember->save();
        }

        if (!$is_success) {
            return redirect($referer);
        } else {
            $request->session()->regenerate();
            $request->session()->put('_user', $dataMember);
            $this->swappingSession($dataMember);
        }

        $action = strtoupper($results['data']['action']);
        if (config('constants.URL.'.$action)) {
            return redirect(config('constants.URL.'.$action));
        }

        return redirect(config('constants.URL.HOME'));

        // switch ($results['data']->group_id) {
        //     case config('constants.GROUPS.SETGROUP'):
        //         # code...
        //         break;
        //     case config('constants.GROUPS.SETLISTEDCOMPANY'):
        //         # code...
        //         break;
        //     case config('constants.GROUPS.SETBROKER'):
        //         # code...
        //         break;
        //     case config('constants.GROUPS.SETSTUDENT'):
        //         # code...
        //         break;
        //     case config('constants.GROUPS.SETEMPLOYEE'):
        //         # code...
        //         break;

        //     default:
        //         # code...
        //         break;
        // }


        // $request->session()->put('_user', );
        // return redirect()->to('http://set-labs.open-cdn.com/', 301)->with('sss', 'sssss');

        // $results['request'] = $request['data'];

        // return response()->json($results, 200);
    }

    public function groupsCoursesLists(Request $request, _SecurityController $_security, _FunctionsController $oFunc, $groups_id)
    {
        $per_page = $request->input('per_page', 10);
        $order_by = $request->input('order_by', 'id');
        $order_direction = $request->input('order_direction', 'DESC');

        $dataGroup = Groups::where('key', $groups_id)->first();

        if (!$dataGroup) {

            $dataReturn = array(
                'group_id' => $groups_id,
                'message' => 'The group "'.$groups_id.'" not found.'
            );

            // $results = $_security->encryptAndSignData(json_encode($dataReturn));
            // return response()->json(array("data" => $results['data'], "signature" => $results['signature']), 404);

            return $response()->json($dataReturn, 404);
        }

        $now = Carbon::now()->toDateTimeString();

        // $data = Courses::where('groups_id', '=', $groups_id)->where('status', '=', '1')->orderBy($order_by,$order_direction)->paginate($per_page);
        // $data = Courses::select('id', 'groups_id', 'code', 'title', 'subject', 'url', 'thumbnail')->where('groups_id', '=', $dataGroup->id)->where('status', '=', '1')->orderBy($order_by,$order_direction)->paginate($per_page);
        // $data = $dataGroup->courses()->select('courses.id', 'courses.code', 'courses.title', 'courses.thumbnail', 'courses.price', 'courses.latest', 'courses.recommended', 'courses.free')->where('courses.status', 1)->paginate($per_page);
        $data = $dataGroup->courses()->select('courses.id', 'courses.code', 'courses.title', 'courses.thumbnail', 'courses.price', 'courses.latest', 'courses.recommended', 'courses.free')
                ->where('courses.start_datetime', '<=', $now)
                ->where('courses.end_datetime', '>', $now)
                ->where('courses.status', 1)->get();

        for ($i=0; $i < count($data); $i++) {
            // unset($data[$i]->modify_by);
            // unset($data[$i]->infomation);
            // unset($data[$i]->objective);
            // unset($data[$i]->suitable);
            // unset($data[$i]->level);
            // unset($data[$i]->Introductory);
            // unset($data[$i]->getting_certificate);
            // unset($data[$i]->getting_certificate_url);
            // unset($data[$i]->more_details);

            if (isset($data[$i]->thumbnail) && $data[$i]->thumbnail != "") {
                $data[$i]->thumbnail = config('constants._BASE_FILE_URL.COURSES_THUMBNAIL').$data[$i]->thumbnail;
            }

            $data[$i]->categories = $data[$i]->categories()->select('categories.id','categories.title', 'categories.hex_color')->get();
            $data[$i]->categories = $oFunc->clearPivot($data[$i]->categories->toArray());

            $data[$i]->latest = $data[$i]->latest == 1 ? true : false;
            $data[$i]->recommended = $data[$i]->recommended == 1 ? true : false;
            $data[$i]->free = $data[$i]->free == 1 ? true : false;

            unset($data[$i]->pivot);
        }

        // return response()->json($data, 200);

        // $results = $_security->encryptAndSignData($data->toJson());
        // return response()->json(array("data" => $results['data'], "signature" => $results['signature']), 200);
        // return response()->json($data, 200);
        return response()->json($data, 200, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    public function groupsCoursesInfo(Request $request, _SecurityController $_security, $group_id, $course_id)
    {
        $results = $_security->verifyData($request->data, $request->signature, env('PATH_SET_WEB_PUBLIC_KEY'));

        if (!$results['isSuccess']) {
            $dataReturn = array(
                'message' => $results['message']
            );

            return response()->json($dataReturn, 500);
        }

        $dataGroup = Groups::where('key', $group_id)->first();

        if (!$dataGroup) {

            $dataReturn = array(
                'group_id' => $group_id,
                'message' => 'The group "'.$group_id.'" not found.'
            );

            // $results = $_security->encryptAndSignData(json_encode($dataReturn));
            // return response()->json(array("data" => $results['data'], "signature" => $results['signature']), 404);

            return response()->json($dataReturn, 404);
        }


        $syncResults = $this->syncMember($results['data']['user_profile']['ref_id'], $results['data'], $dataGroup);

        if ($syncResults['is_success']) {
            $request->session()->put('_user', $syncResults['dataMember']);
        }

        $action = strtoupper($results['data']['action']);
        $urlAction = config('constants.URL.'.$action);

        if ($urlAction) {
            return redirect(str_replace("{COURSE_ID}", $course_id, $urlAction));
        }

        return redirect(str_replace("{COURSE_ID}", $course_id, config('constants.URL.INFO')));

    }


    public function dummyData(Request $request, _SecurityController $_security)
    {
        $results = $_security->encryptAndSignData(json_encode($request->all(), JSON_UNESCAPED_UNICODE));
        return response()->json(array("data" => $results['data'], "signature" => $results['signature']), 200);
    }


    public function getSessionUser()
    {
        $data = session()->get('_user');

        // $debug = session()->getId();

        // $agent = new Agent();
        // $debug = $agent->getUserAgent();

        return response()->json(array("data" => $data, "debug_time" => time()), 200);
        // return response()->json(array("data" => $data, "debug" => $debug), 200);
    }

    private function swappingSession($user) {
        $new_sessid   = session()->getId(); //get new my_session_id after user sign in

        if ($user->my_session_id) {
            $last_session = session()->getHandler()->read($user->my_session_id); // retrive last session

            if ($last_session) {
                if (session()->getHandler()->destroy($user->my_session_id)) {
                    // session was destroyed
                }
            }
        }

        $user->my_session_id = $new_sessid;
        $user->save();
    }

    public function testThaiLanguage(Request $request, _SecurityController $_security)
    {
        // return response($request->all());
        return response()->json($request->all(), 200, ['Content-type'=> 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }


}
