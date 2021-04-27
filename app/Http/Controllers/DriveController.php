<?php
 
namespace App\Http\Controllers;

use App\Models\AtividadeAcademica;
use App\Models\User;
use Exception;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Drive_Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
 
class DriveController extends Controller
{
   private $drive;
   public function __construct(Google_Client $client)
   {
       $this->middleware(function ($request, $next) use ($client) {
           $accessToken = [
               'access_token' => auth()->user()->token,
               'created' => auth()->user()->created_at->timestamp,
               'expires_in' => auth()->user()->expires_in,
               'refresh_token' => auth()->user()->refresh_token
           ];
   
           $client->setAccessToken($accessToken);
   
           if ($client->isAccessTokenExpired()) {
               if ($client->getRefreshToken()) {
                   $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
               }
               auth()->user()->update([
                   'token' => $client->getAccessToken()['access_token'],
                   'expires_in' => $client->getAccessToken()['expires_in'],
                   'created_at' => $client->getAccessToken()['created'],
               ]);
           }
   
           $client->refreshToken(auth()->user()->refresh_token);
           $this->drive = new Google_Service_Drive($client);
           return $next($request);
       });
   }

   function createFolder($folder_name, $parent){
        $folder_meta = new Google_Service_Drive_DriveFile(array(
            'name' => $folder_name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => array($parent),
        ));
        $folder = $this->drive->files->create($folder_meta, array(
            'fields' => 'id'));
        return $folder->id;
    }

    function createFile($files, $atividade){

        foreach ($files as $file) {
            $name = gettype($file) === 'object' ? $file->getClientOriginalName() : $file;
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => $name,
                'parents' => array($atividade->folder_id),
            ]);

            $content = gettype($file) === 'object' ?  File::get($file) : Storage::get($file);
            $mimeType = gettype($file) === 'object' ? File::mimeType($file) : Storage::mimeType($file);
    
            $file = $this->drive->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id'
            ]);


        }

        $ret = redirect()->route('verAtividade.verArquivos', ['atividade_id' => $atividade->id]);
        return $ret;
    }

    function uploadFile(Request $request, $atividade_id){
        $atividade = AtividadeAcademica::find($atividade_id);
        return $this->createFile($request->file('arquivo'), $atividade);

    }

    function grantPermission($role, $user, $atividade){
        $userPermission = new Google_Service_Drive_Permission(array(
            'type' => 'user',
            'role' => $role,
            'emailAddress' => $user->email,
            //'parents' => array($user->folder_id_minhas_atividades),
        ));

        $request = $this->drive->permissions->create($atividade->folder_id, $userPermission, array('fields' => 'id'));
        
        $ret = redirect()->route('verAtividade.verPessoas', ['atividade_id' => $atividade->id]);
        return $ret;
    }

    function listarArquivosPasta($folderId){
        // $pageToken = NULL;
        // do{
        //     $parameters = array();
        //     if ($pageToken) {
        //         $parameters['pageToken'] = $pageToken;
        //     }
        //     $children = $this->drive->children->listChildren($folderId, $parameters);
        //     $pageToken = $children->getNextPageToken();
        // }while($pageToken);
        // return $children->getItems();
    }
}
