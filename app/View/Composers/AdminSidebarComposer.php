<?php

namespace App\View\Composers;

use App\Models\DownloadLog;
use Illuminate\View\View;

class AdminSidebarComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $unseenDownloadLogsCount = DownloadLog::where('is_seen', false)->count();
        $view->with('unseenDownloadLogsCount', $unseenDownloadLogsCount);
    }
}
