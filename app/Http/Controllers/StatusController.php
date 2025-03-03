<?
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class StatusController extends Controller
{
    public function runCommand()
    {
        Artisan::call('statuses:run');
        $output = Artisan::output();

        return response()->json([
            'message' => 'Command executed successfully',
            'output' => $output,
        ], 200);
    }
}
