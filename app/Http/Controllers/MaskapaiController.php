<?php

namespace App\Http\Controllers;

use App\BusAdmin;
use App\Bus;
use App\TopUpRequest;
use App\TripHistory;
use Illuminate\Http\Request;
use App\Repo\BusRepoImpl;
use Illuminate\Support\Facades\Storage;
use Exception;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class MaskapaiController extends Controller {
    // Session KEY
    const SESS_USER = "user";

    private $bus_repo;

    public function __construct() {
        $this->bus_repo = new BusRepoImpl();
    }

    public function home() {
        return view('maskapai/home', ['title' => $_ENV['APP_NAME']]);
    }

    public function login(Request $req) {
        $email = $req->input('email');
        $password = $req->input('password');

        if($email == "" || $password == "") {
            // set error
            return response()->json([
                'error' => 'Email atau password tidak boleh kosong'
            ], 403);
        }

        $busAdmin = BusAdmin::where('email', $email)->first();

        if($busAdmin == null) {
            // set error
            return response()->json([
                'error' => 'Akun tidak terdaftar'
            ], 403);
        }

        if(!app('hash')->check($password, $busAdmin->password)) {
            return response()->json([
                'error' => 'Email atau katasandi salah, mohon dicoba lagi'
            ], 403);
        }

        // save user id in session  
        $req->session()->put("user", ['id' => $busAdmin->id]);

        return redirect()->route('dashboard');
    }

    public function dashboard(Request $req) {
        // get session
        $id = $req->session()->get("user")['id'];
        $busses = $this->bus_repo->get_busses($id);
        
        if(count($busses) <= 0) {
            // set empty
            return view('maskapai/empty_dashboard');
        }

        return view('maskapai/dashboard', ['busses' => $busses]);    
    }

    public function add_bus(Request $req) {
        $bus_number = $req->input('bus_number');
        $price = $req->input('price');
        $bus_admin_id = $req->session()->get("user")['id'];

        // validator
        // ...

        $bus_data = [
            'bus_number' => $bus_number, 
            'price' => $price, 
            'bus_admin_id' => $bus_admin_id
        ];
        
        try {
            $bus = Bus::create($bus_data);
            
            // create qr code
            $qr = new ImageRenderer(
                new RendererStyle(400),
                new ImagickImageBackEnd()
            );

            $writer = new Writer($qr);

            // save to Storage
            Storage::put("qr/{$bus->id}.png", $writer->writeString("{$bus->id}"));

            $busses = $this->bus_repo->get_busses($bus_admin_id);
            return view('maskapai/dashboard', ['busses' => $busses]);
        } catch (Exception $e) {
            dd($e);
            // log the exception
            return response()->json(['error' => 'Sistem bermasalah'], 400);
        }
    }

    public function delete_bus(Request $req) {
        $id = $req->input('id');
        try {
            $bus = Bus::where('id', $id)->delete();
        } catch (\Throwable $th) {
            // loggin exception
        }

        $bus_admin_id = $req->session()->get("user")['id'];
        $busses = $this->bus_repo->get_busses($bus_admin_id);
        return view('maskapai/dashboard', ['busses' => $busses]);
    }

    public function edit_bus(Request $req) {
        $id = $req->input('id');
        
        $bus = null;
        
        try {
            $bus = Bus::where('id', $id)->firstOrFail();
        } catch(Exception $e) {
            // logging the exception
            var_dump($e);
        }

        return view('maskapai/edit', ['bus' => $bus]);
    }

    public function save_edit(Request $req) {
        $bus_number = $req->input('bus_number');
        $price = $req->input('price');
        $id = $req->input('bus_id');

        try {
            $bus = Bus::where('id', $id)
                ->update([
                    'bus_number' => $bus_number,
                    'price' => $price
                ]);
        } catch (Exception $e) {
            //throw $th;
            echo "<pre>";
            var_dump($e);
        }

        $bus_admin_id = $req->session()->get("user")['id'];

        $busses = $this->bus_repo->get_busses($bus_admin_id);
        return view('maskapai/dashboard', ['busses' => $busses]);
    }

    public function download_qr(Request $req) {
        $bus_id = $req->input('bus_id');
        $file_path = "qr/{$bus_id}.png";

        try {
            // $file = Storage::get($file_path);
            return Storage::download($file_path);
        } catch (Exception $e) {
            var_dump($e);
        }
    }
}