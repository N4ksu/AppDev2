<?php

namespace Tests\Unit;

use App\Jobs\AnalyzeSecuritySequenceJob;
use Tests\TestCase;

class AnalyzeSecuritySequenceJobTest extends TestCase
{
    public function test_job_has_dedupe_key_and_retry_configuration(): void
    {
        $job = new AnalyzeSecuritySequenceJob(123, 2);

        $this->assertSame('123:2', $job->uniqueId());
        $this->assertSame(3, $job->tries);
        $this->assertSame([30, 120, 300], $job->backoff);
    }
}
