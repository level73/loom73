<?php

declare(strict_types=1);

namespace Loom73\Tests\Unit\Beam;

use Loom73\Beam\Model;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;
use InvalidArgumentException;

/**
 * Verifies identifier handling through public CRUD methods without a database.
 * Invalid field names must be rejected before PDO prepares a statement.
 */
final class ModelIdentifierTest extends TestCase
{
    public function testOrderingQuotesIdentifiersAndNormalizesDirection(): void
    {
        $rows = [(object) ['idarticles' => 1]];

        $statement = $this->createStub(PDOStatement::class);
        $statement->method('execute')->willReturn(true);
        $statement->method('rowCount')->willReturn(1);
        $statement->method('fetchAll')->willReturn($rows);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with('SELECT * FROM `articles` ORDER BY `created_at` DESC')
            ->willReturn($statement);

        $result = $this->model($pdo)->all('created_at', 'desc');

        self::assertSame($rows, $result->all());
    }

    public function testCreateRejectsSqlFragmentsAsFieldNames(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::never())->method('prepare');

        $model = $this->model($pdo);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid SQL identifier: title; DROP TABLE articles'
        );

        $model->create(['title; DROP TABLE articles' => 'Example']);
    }

    /**
     * A trailing newline is outside the documented identifier character set.
     */
    public function testCreateRejectsFieldNamesWithATrailingNewline(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::never())->method('prepare');

        $model = $this->model($pdo);

        $this->expectException(InvalidArgumentException::class);

        $model->create(["title\n" => 'Example']);
    }

    /**
     * Create a fake "articles" model, because the Model class is abstract
     * @param PDO $pdo
     * @return Model
     */
    private function model(PDO $pdo): Model
    {
        return new class($pdo) extends Model {
            protected string $table = 'articles';
        };
    }

    public function testAllRejectsInvalidTableNames(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::never())->method('prepare');

        $model = new class($pdo) extends Model {
            protected string $table = 'articles; DROP TABLE users';
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid SQL identifier: articles; DROP TABLE users'
        );

        $model->all();
    }

    public function testAllRejectsSqlFragmentsAsOrderingColumns(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::never())->method('prepare');

        $model = $this->model($pdo);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid SQL identifier: created_at` DESC --'
        );

        $model->all('created_at` DESC --');
    }

    public function testAllRejectsUnsupportedSortDirections(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::never())->method('prepare');

        $model = $this->model($pdo);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid SQL direction: DESC; DROP TABLE ARTICLES'
        );

        $model->all('created_at', 'DESC; DROP TABLE articles');
    }
}