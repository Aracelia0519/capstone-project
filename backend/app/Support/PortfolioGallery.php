<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Builds the before/after image data for provider portfolios.
 *
 * Before images come from service_survey_agreements.proof_of_visitation.
 * After images come from approved completion submissions of completed jobs.
 */
class PortfolioGallery
{
    public static function completedJobImages(int $providerId, string $baseUrl): array
    {
        $completions = DB::table('service_job_completions')
            ->join('client_service_requests', 'service_job_completions.client_service_request_id', '=', 'client_service_requests.id')
            ->where('client_service_requests.provider_id', $providerId)
            ->where('client_service_requests.status', 'completed')
            ->where('service_job_completions.status', 'approved')
            ->select(
                'service_job_completions.id as completion_id',
                'service_job_completions.client_service_request_id',
                'service_job_completions.proof_images'
            )
            ->orderBy('service_job_completions.id')
            ->get();

        $requestIds = $completions->pluck('client_service_request_id')->unique()->values()->all();
        $surveyAgreements = empty($requestIds)
            ? collect()
            : DB::table('service_survey_agreements')
                ->whereIn('client_service_request_id', $requestIds)
                ->orderByDesc('id')
                ->get()
                ->unique('client_service_request_id')
                ->keyBy('client_service_request_id');

        $images = [];
        foreach ($completions as $completion) {
            $proofs = json_decode($completion->proof_images, true);
            if (!is_array($proofs) || empty($proofs)) {
                continue;
            }

            $agreement = $surveyAgreements->get($completion->client_service_request_id);
            $beforePath = $agreement->proof_of_visitation ?? null;
            $beforeUrl = self::publicUrl($beforePath, $baseUrl);

            foreach ($proofs as $index => $path) {
                if (!is_string($path) || $path === '') {
                    continue;
                }

                $images[] = [
                    'path' => $path,
                    'url' => self::publicUrl($path, $baseUrl),
                    'before_path' => $beforePath,
                    'before_url' => $beforeUrl,
                    'completion_id' => $completion->completion_id,
                    'client_service_request_id' => $completion->client_service_request_id,
                    'is_first' => $index === 0,
                ];
            }
        }

        return $images;
    }

    public static function publicUrl(?string $path, string $baseUrl): ?string
    {
        if (!$path) {
            return null;
        }

        $cleanPath = preg_replace('/^\/?storage\//', '', $path);

        return $baseUrl . '/storage/' . ltrim($cleanPath, '/');
    }
}
