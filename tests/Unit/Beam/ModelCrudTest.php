<?php

declare(strict_types=1);

namespace Loom73\Tests\Unit\Beam;
use Loom73\Beam\Model;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;
use PDOException;
use InvalidArgumentException;


/**
 * Verifies CRUD SQL, parameter binding and result propagation without a database.
 * PDO doubles capture bound values and provide controlled statement results.
 */
final class ModelCrudTest extends TestCase
{
    public function testGetByIdBindsAnIntegerAndReturnsTheFetchedObject(): void
    {
        $row = (object)['idarticles' => 42, 'title' => 'Example'];

        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindValue')
            ->with(':id', 42, PDO::PARAM_INT)
            ->willReturn(true);
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(true);
        $statement->method('rowCount')->willReturn(1);
        $statement->expects(self::once())
            ->method('fetchAll')
            ->with(PDO::FETCH_OBJ)
            ->willReturn([$row]);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with('SELECT * FROM `articles` WHERE `idarticles` = :id LIMIT 1')
            ->willReturn($statement);

        $result = $this->model($pdo)->getById(42);

        self::assertTrue($result->passes());
        self::assertSame(1, $result->rows);
        self::assertSame($row, $result->first());
    }

    /**
     * Values remain bound data; inferred types and explicit overrides are preserved.
     */
    public function testCreateBindsValuesWithTheirTypesAndReturnsTheInsertId(): void
    {
        $bindings = [];

        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::exactly(5))
            ->method('bindValue')
            ->willReturnCallback(
                static function (
                    string $placeholder,
                    mixed  $value,
                    int    $type
                ) use (&$bindings): bool {
                    $bindings[$placeholder] = [$value, $type];

                    return true;
                }
            );
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(true);
        $statement->method('rowCount')->willReturn(1);
        $statement->expects(self::never())->method('fetchAll');

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with(
                'INSERT INTO `articles` (`title`, `views`, `published`, `summary`, `code`)'
                . ' VALUES (:title, :views, :published, :summary, :code)'
            )
            ->willReturn($statement);
        $pdo->expects(self::once())
            ->method('lastInsertId')
            ->willReturn('42');

        $result = $this->model($pdo)->create([
            'title' => "Moby Dick or The Whale: a modern read",
            'views' => 3,
            'published' => true,
            'summary' => null,
            'code' => [
                'value' => 42,
                'type' => PDO::PARAM_STR,
            ],
        ]);

        self::assertSame([
            ':title' => ["Moby Dick or The Whale: a modern read", PDO::PARAM_STR],
            ':views' => [3, PDO::PARAM_INT],
            ':published' => [true, PDO::PARAM_BOOL],
            ':summary' => [null, PDO::PARAM_NULL],
            ':code' => [42, PDO::PARAM_STR],
        ], $bindings);
        self::assertTrue($result->passes());
        self::assertSame(1, $result->rows);
        self::assertSame(42, $result->insertId);
        self::assertTrue($result->isEmpty());
    }
    /**
     * SET and WHERE values for the same column must use independent parameters.
     */
    public function testUpdateWhereKeepsNewValuesSeparateFromConditions(): void
    {
        $bindings = [];

        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::exactly(2))
            ->method('bindValue')
            ->willReturnCallback(
                static function (
                    string $placeholder,
                    mixed $value,
                    int $type
                ) use (&$bindings): bool {
                    $bindings[$placeholder] = [$value, $type];

                    return true;
                }
            );
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(true);
        $statement->method('rowCount')->willReturn(3);
        $statement->method('fetchAll')->willReturn([]);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with(
                'UPDATE `articles` SET `status` = :set_status'
                . ' WHERE `status` = :where_status'
            )
            ->willReturn($statement);
        $pdo->expects(self::never())->method('lastInsertId');

        $result = $this->model($pdo)->updateWhere(
            ['status' => 2],
            ['status' => 1]
        );

        self::assertSame([
            ':set_status' => [2, PDO::PARAM_INT],
            ':where_status' => [1, PDO::PARAM_INT],
        ], $bindings);
        self::assertTrue($result->passes());
        self::assertSame(3, $result->rows);
        self::assertTrue($result->isEmpty());
        self::assertNull($result->insertId);
    }

    /**
     * A successful DELETE can affect no rows when the requested record is absent.
     */
    public function testDeleteByIdBindsAnIntegerAndAllowsZeroAffectedRows(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindValue')
            ->with(':id', 42, PDO::PARAM_INT)
            ->willReturn(true);
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(true);
        $statement->method('rowCount')->willReturn(0);
        $statement->method('fetchAll')->willReturn([]);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->with('DELETE FROM `articles` WHERE `idarticles` = :id')
            ->willReturn($statement);
        $pdo->expects(self::never())->method('lastInsertId');

        $result = $this->model($pdo)->deleteById(42);

        self::assertTrue($result->passes());
        self::assertSame(0, $result->rows);
        self::assertTrue($result->isEmpty());
        self::assertNull($result->insertId);
    }

    public function testUpdateWhereRejectsEmptyConditionsBeforePreparingSql(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::never())->method('prepare');

        $model = $this->model($pdo);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Update conditions cannot be empty.');

        $model->updateWhere(['status' => 2], []);
    }

    public function testUpdateWhereRejectsEmptyDataBeforePreparingSql(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::never())->method('prepare');

        $model = $this->model($pdo);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Update data cannot be empty.');

        $model->updateWhere([], ['status' => 1]);
    }

    public function testPreparationExceptionBecomesAFailedQueryResult(): void
    {
        $error = new PDOException('Preparation failed.');

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::once())
            ->method('prepare')
            ->willThrowException($error);

        $result = $this->model($pdo)->getById(42);

        self::assertTrue($result->fails());
        self::assertSame($error, $result->exception());
    }

    /**
     * Failed execution must preserve the exception and stop result retrieval.
     */
    public function testExecutionExceptionBecomesAFailedQueryResult(): void
    {
        $error = new PDOException('Execution failed.');

        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindValue')
            ->with(':id', 42, PDO::PARAM_INT)
            ->willReturn(true);
        $statement->expects(self::once())
            ->method('execute')
            ->willThrowException($error);
        $statement->expects(self::never())->method('rowCount');
        $statement->expects(self::never())->method('fetchAll');

        $pdo = $this->createStub(PDO::class);
        $pdo->method('prepare')->willReturn($statement);

        $result = $this->model($pdo)->getById(42);

        self::assertTrue($result->fails());
        self::assertSame($error, $result->exception());
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
}
