<?php

declare(strict_types=1);

namespace Loom73\Tests\Unit\Beam;

use Loom73\Beam\QueryResult;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use PDOException;

/**
 * Verifies the distinction between failed operations, successful empty results
 * and successful results containing fetched objects.
 *
 * Results are created in memory; no database connection is required.
 */
final class QueryResultTest extends TestCase
{
    public function testSuccessfulEmptyResultIsNotAFailure(): void
    {
        $result = QueryResult::success();

        self::assertTrue($result->passes());
        self::assertFalse($result->fails());
        self::assertTrue($result->isEmpty());
        self::assertNull($result->first());
    }

    public function testSuccessfulResultPreservesReturnedObjectsAndOrder(): void
    {
        $first = (object) ['id' => 1, 'name' => 'First'];
        $second = (object) ['id' => 2, 'name' => 'Second'];

        $result = QueryResult::success(
            rows: 2,
            data: [$first, $second]
        );

        self::assertTrue($result->hasData());
        self::assertFalse($result->isEmpty());
        self::assertSame($first, $result->first());
        self::assertSame([$first, $second], $result->all());
        self::assertSame(2, $result->count());
    }

    /**
     * Callers must retain access to the original exception and its diagnostics.
     */
    public function testFailurePreservesTheOriginalException(): void
    {
        $error = new RuntimeException('Database operation failed.', 42);

        $result = QueryResult::failure($error);

        self::assertTrue($result->fails());
        self::assertFalse($result->passes());
        self::assertSame($error, $result->exception());
        self::assertSame('Database operation failed.', $result->errorMessage());
        self::assertSame(42, $result->errorCode());
    }

    /**
     * A write can affect rows and expose an insert ID without returning records.
     */
    public function testSuccessfulWriteDoesNotRequireFetchedData(): void
    {
        $result = QueryResult::success(rows: 1, insertId: 42);

        self::assertTrue($result->passes());
        self::assertTrue($result->isEmpty());
        self::assertSame(1, $result->rows);
        self::assertSame(0, $result->count());
        self::assertSame(42, $result->insertId);
    }

    /**
     * Fetched records are counted independently of the driver's row count.
     */
    public function testFetchedCountDoesNotDependOnReportedRowCount(): void
    {
        $result = QueryResult::success(
            rows: 0,
            data: [
                (object) ['id' => 1],
                (object) ['id' => 2],
            ]
        );

        self::assertSame(0, $result->rows);
        self::assertSame(2, $result->count());
    }

    /**
     * SQLSTATE and native driver error codes must remain distinguishable.
     */
    public function testDatabaseErrorCodeUsesTheNativeDriverCode(): void
    {
        $error = new PDOException('Duplicate entry.', 23000);
        $error->errorInfo = ['23000', 1062, 'Duplicate entry.'];

        $result = QueryResult::failure($error);

        self::assertSame(23000, $result->errorCode());
        self::assertSame(1062, $result->databaseErrorCode());
    }

    public function testDatabaseErrorCodeFallsBackToTheExceptionCode(): void
    {
        $error = new PDOException('Driver code unavailable.', 23000);

        $result = QueryResult::failure($error);

        self::assertSame(23000, $result->databaseErrorCode());
    }
}
