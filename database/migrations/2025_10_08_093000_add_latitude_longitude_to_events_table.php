<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function(Blueprint $table){
            if(!Schema::hasColumn('events','latitude')){
                $table->decimal('latitude', 10, 7)->nullable()->after('google_maps_url');
            }
            if(!Schema::hasColumn('events','longitude')){
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            // index for proximity queries
            if(!collect(Schema::getColumnListing('events'))->contains(fn($c)=>$c==='events_lat_lng_index')){
                $table->index(['latitude','longitude'],'events_lat_lng_index');
            }
        });

        // Backfill existing coordinates from stored google_maps_url
        $rows = DB::table('events')->select('id','google_maps_url')->whereNotNull('google_maps_url')->get();
        foreach($rows as $row){
            $url = $row->google_maps_url;
            if(!$url) continue;
            $patterns = [
                '/@(-?[0-9]{1,3}\.[0-9]+),(-?[0-9]{1,3}\.[0-9]+)/',
                '/[?&]q=(-?[0-9]{1,3}\.[0-9]+),(-?[0-9]{1,3}\.[0-9]+)/',
                '/\/(-?[0-9]{1,3}\.[0-9]+),(-?[0-9]{1,3}\.[0-9]+)(?:\/|$)/'
            ];
            foreach($patterns as $p){
                if(preg_match($p,$url,$m)){
                    $lat=(float)$m[1]; $lng=(float)$m[2];
                    if($lat<=90 && $lat>=-90 && $lng<=180 && $lng>=-180){
                        DB::table('events')->where('id',$row->id)->update(['latitude'=>$lat,'longitude'=>$lng]);
                    }
                    break;
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('events', function(Blueprint $table){
            if(Schema::hasColumn('events','latitude')) $table->dropColumn('latitude');
            if(Schema::hasColumn('events','longitude')) $table->dropColumn('longitude');
            try { $table->dropIndex('events_lat_lng_index'); } catch (Throwable $e) {}
        });
    }
};
