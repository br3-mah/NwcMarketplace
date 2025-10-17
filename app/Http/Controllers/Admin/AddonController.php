<?php

namespace App\Http\Controllers\Admin;

use DB;
use Datatables;
use App\Models\Addon;
use Illuminate\Http\Request;


class AddonController extends AdminBaseController
{

    //*** JSON Request
    public function datatables()
    {
         $datas = Addon::get();
         //--- Integrating This Collection Into Datatables
         return Datatables::of($datas)
                            ->editColumn('created_at', function(Addon $data) {
                                return date('Y-m-d',strtotime($data->created_at));
                            })
                            ->addColumn('action', function(Addon $data) {

                                return '<div class="action-list"><a href="javascript:;" data-href="' . route('admin-addon-uninstall',$data->id) . '" data-toggle="modal" data-target="#confirm-status"> <i class="fas fa-edit"></i>'.__("Uninstall").'</a></div>';
                            }) 
                            ->rawColumns(['action'])
                            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function index(){
        return view('admin.addon.index');
    }


    public function create(){
        return view('admin.addon.create');
    }

    //*** GET Request Status
    public function install(Request $request)
    {

        $request->validate([
            'file' => 'required|mimes:zip',
        ]);

        $zip = new \ZipArchive();  
        $res = $zip->open($request->file);  
        $zip->extractTo('assets/addons');  
        $zip->close();

        if(file_exists(public_path().'/assets/addons/config.json')){

            $getFile = file_get_contents(public_path().'/assets/addons/config.json');
            $file = json_decode($getFile, true);

            if(!DB::table('addons')->whereKeyword($file['plugin_keyword'])->exists()){

                foreach($file['code'] as $code){
                    DB::statement($code);
                }

                foreach($file['files'] as $tfile){
                    if(file_exists(public_path().$tfile['entry-point'])){
                        rename(public_path().$tfile['entry-point'],base_path().$tfile['exit-point']);
                        rmdir($tfile['folder-path']);
                    }
                }

                unlink(public_path().'/assets/addons/config.json');

                //--- Redirect Section
                $msg = __('Addon Installed Successfully.');
                return redirect()->route('admin-addon-index')->withSuccess($msg);
                //--- Redirect Section Ends

            }
            else{
                $this->deleteDir(public_path().'/assets/addons');
                mkdir('assets/addons', 0777, true);
                return redirect()->back()->withUnsuccess(__('This Addon Is Already Installer.'));
            }

        }else{
            $this->deleteDir(public_path().'/assets/addons');
            mkdir('assets/addons', 0777, true);
            return redirect()->back()->withUnsuccess(__('Invalid File Format.'));
        }

    }

    //*** GET Request Status
    public function uninstall($id)
    {
        $data = Addon::findOrFail($id);
        
        $files = json_decode($data->uninstall_files, true);

        foreach($files['files'] as $file){
            if(file_exists(base_path().$file)){
                unlink(base_path().$file);
            }
        }

        foreach($files['codes'] as $code){
            DB::statement($code);
        }

        $data->delete();

        //--- Redirect Section
        $msg = __('Addon Uninstalled Successfully.');
        return redirect()->back()->withSuccess($msg);
        //--- Redirect Section Ends
    }

    public function deleteDir($dir) {
        foreach(glob($dir . '/*') as $file) {
            if(is_dir($file))
                $this->deleteDir($file);
            else
                unlink($file);
        }
        rmdir($dir);
    }

}