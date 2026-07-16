<?php
namespace App\Utilities\Security;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

trait SystemSecurity {
    private const string DIR_SECURITY = "security";
    private const string FILE_BLACKLIST = "blacklist.json";
    
    public function isBlacklisted(string $ip): bool {
        $blacklist_path = self::DIR_SECURITY . "/" . self::FILE_BLACKLIST;
        
        // No Blacklist File Means No One Is Blacklisted.
        if (!Storage::disk("local")->exists($blacklist_path)) {
            return false;
        }
        
        $this->removeBlacklist();
        $blacklists = json_decode(Storage::disk("local")->get($blacklist_path), true) ?? [];
        
        foreach ($blacklists as $blacklist) {
            // Clear Invalid Blacklist Records.
            if (!isset($blacklist["ip_address"])) {
                continue;
            }
            
            // Check If The IP Is Blacklisted.
            if ($blacklist["ip_address"] === $ip) {
                return true;
            }
        }
        
        return false;
    }
    
    public function applyBlacklist(): bool {
        $ip = request()->ip();
        $blacklist_path = self::DIR_SECURITY . "/" . self::FILE_BLACKLIST;
        
        if (!Storage::disk("local")->exists($blacklist_path)) {
            if (!Storage::disk("local")->exists(self::DIR_SECURITY)) {
                Storage::disk("local")->makeDirectory(self::DIR_SECURITY);
            }
            Storage::disk("local")->put($blacklist_path, json_encode([]));
        }
        
        // Check If The IP Is Blacklisted.
        if ($this->isBlacklisted($ip)) {
            return true;
        }
        
        $blacklists = json_decode(Storage::disk("local")->get($blacklist_path), true) ?? [];
        $blacklists[] = [
            "ip_address" => $ip,
            "blacklist_at" => Carbon::now()->toDateTimeString(),
        ];
        
        Storage::disk("local")->put($blacklist_path, json_encode($blacklists, JSON_PRETTY_PRINT));
        return true;
    }
    
    public function removeBlacklist(): void {
        $blacklist_path = self::DIR_SECURITY . "/" . self::FILE_BLACKLIST;
        
        if (!Storage::disk("local")->exists($blacklist_path)) {
            return;
        }
        
        $blacklists = json_decode(Storage::disk("local")->get($blacklist_path), true) ?? [];
        $current_datetime = Carbon::now();
        $updated_blacklists = [];
        $has_changes = false;
        
        foreach ($blacklists as $blacklist) {
            // Clear Invalid Blacklist Records.
            if (!isset($blacklist["ip_address"], $blacklist["blacklist_at"])) {
                $has_changes = true;
                continue;
            }
            
            $is_expired = Carbon::parse($blacklist["blacklist_at"])
                ->addHours(24)
                ->lessThanOrEqualTo($current_datetime);
            
            // Clear Expired Blacklist Records.
            if ($is_expired) {
                $has_changes = true;
                continue;
            }
            
            $updated_blacklists[] = $blacklist;
        }
        
        if (!$has_changes) {
            return;
        }
        
        Storage::disk("local")->put($blacklist_path, json_encode($updated_blacklists, JSON_PRETTY_PRINT));
    }
}