<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');

        // Reports can return very large Inertia payloads; Telescope recording can push
        // memory over the limit during termination. Disable recording for these routes.
        if ($isLocal && !$this->app->runningInConsole()) {
            try {
                $path = request()->path();
                if (str_starts_with($path, 'reports')) {
                    Telescope::stopRecording();
                }
            } catch (\Throwable $e) {
                // Ignore if request isn't available (e.g., early boot).
            }
        }

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
            if ($isLocal) {
                // Extra guard: never store report requests even if recording is re-enabled.
                if ($entry->type === 'request') {
                    $uri = (string) data_get($entry->content, 'uri', '');
                    if (str_contains($uri, '/reports')) return false;
                }
                return true;
            }

            return
                   $entry->isReportableException() ||
                   $entry->isFailedRequest() ||
                   $entry->isFailedJob() ||
                   $entry->isScheduledTask() ||
                   $entry->hasMonitoredTag();
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }
}
