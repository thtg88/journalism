<?php

namespace Thtg88\Journalism\Tests\Feature;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Thtg88\Journalism\Helpers\JournalEntryHelper;
use Thtg88\Journalism\JournalismServiceProvider;
use Thtg88\Journalism\Models\JournalEntry;
use Thtg88\Journalism\Tests\TestClasses\Models\TestModel;
use Thtg88\Journalism\Tests\TestClasses\Models\User;

/**
 * @psalm-suppress UnusedClass
 */
class JournalismTest extends BaseTestCase
{
    private JournalEntryHelper $helper;
    private TestModel $model;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('password');
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('secret')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->softDeletes();
            $table->timestamps();
        });

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $this->artisan('migrate', [
            '--path' => [__DIR__.'/../../database/migrations'],
        ]);

        $this->helper = app()->make(JournalEntryHelper::class);
        /** @var \Thtg88\Journalism\Tests\TestClasses\Models\TestModel */
        $this->model = TestModel::factory()->create();

        // This is so that the correct target type can be set on journal entries table
        Relation::morphMap(['test_models' => TestModel::class]);
    }

    /**
     * Load package service provider.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    #[\Override]
    protected function getPackageProviders($app)
    {
        return [JournalismServiceProvider::class];
    }

    /**
     * @test
     *
     * @covers \Thtg88\Journalism\Helpers\JournalEntryHelper::createJournalEntry
     */
    public function action_gets_set_test(): void
    {
        $expected = 'ABCD';

        $journal_entry = $this->helper->createJournalEntry($expected);

        $this->assertInstanceOf(JournalEntry::class, $journal_entry);
        $this->assertEquals($expected, $journal_entry->action);
    }

    /**
     * @test
     *
     * @covers \Thtg88\Journalism\Helpers\JournalEntryHelper::createJournalEntry
     */
    public function user_id_is_null_if_not_logged_in_test(): void
    {
        $expected = null;

        $journal_entry = $this->helper->createJournalEntry('ABCD');

        $this->assertInstanceOf(JournalEntry::class, $journal_entry);
        $this->assertEquals($expected, $journal_entry->user_id);
        $this->assertEquals($expected, $journal_entry->user);
    }

    /**
     * @test
     *
     * @covers \Thtg88\Journalism\Helpers\JournalEntryHelper::createJournalEntry
     */
    public function content_is_null_if_not_provided(): void
    {
        $expected = null;

        $journal_entry = $this->helper->createJournalEntry('ABCD');

        $this->assertInstanceOf(JournalEntry::class, $journal_entry);
        $this->assertEquals($expected, $journal_entry->content);
    }

    /**
     * @test
     *
     * @covers \Thtg88\Journalism\Helpers\JournalEntryHelper::createJournalEntry
     */
    public function no_model_does_not_set_target_id_and_target_type_test(): void
    {
        $journal_entry = $this->helper->createJournalEntry('ABCD');

        $this->assertInstanceOf(JournalEntry::class, $journal_entry);
        $this->assertNull($journal_entry->target_id);
        $this->assertNull($journal_entry->target_type);
        $this->assertNull($journal_entry->target);
    }

    /**
     * @test
     *
     * @covers \Thtg88\Journalism\Helpers\JournalEntryHelper::createJournalEntry
     */
    public function with_model_set_target_id_and_target_type_test(): void
    {
        $actual = $this->helper->createJournalEntry(
            'ABCD',
            $this->model
        );

        $this->assertInstanceOf(JournalEntry::class, $actual);
        $this->assertNotNull($actual->target_id);
        $this->assertNotNull($actual->target_type);
        $this->assertNotNull($actual->target);
        $this->assertTrue($actual->target->is($this->model));
        $this->assertEquals($this->model->id, $actual->target_id);
        $this->assertEquals($this->model->getTable(), $actual->target->getTable());
    }

    /**
     * @test
     *
     * @covers \Thtg88\Journalism\Helpers\JournalEntryHelper::createJournalEntry
     */
    public function logged_in_user_gets_set_test(): void
    {
        /** @var \Thtg88\Journalism\Tests\TestClasses\Models\User $expected */
        $expected = User::factory()->create();
        Auth::login($expected);

        $journal_entry = $this->helper->createJournalEntry('ABCD');

        $this->assertInstanceOf(JournalEntry::class, $journal_entry);
        $this->assertEquals($expected->id, $journal_entry->user_id);

        Auth::logout();
    }

    /**
     * @test
     *
     * @covers \Thtg88\Journalism\Helpers\JournalEntryHelper::createJournalEntry
     */
    public function content_gets_set(): void
    {
        $expected = ['foo' => 'bar'];

        $journal_entry = $this->helper->createJournalEntry(
            'ABCD',
            null,
            $expected,
        );

        $this->assertInstanceOf(JournalEntry::class, $journal_entry);
        $this->assertIsArray($journal_entry->content);
        $this->assertEquals($expected, $journal_entry->content);
    }

    /**
     * @test
     *
     * @covers \Thtg88\Journalism\Helpers\JournalEntryHelper::createJournalEntry
     */
    public function content_does_not_contain_secret_if_model_provided(): void
    {
        $expected = ['secret' => 'password'];

        $journal_entry = $this->helper->createJournalEntry(
            'ABCD',
            $this->model,
            $expected,
        );

        $this->assertInstanceOf(JournalEntry::class, $journal_entry);
        $this->assertIsArray($journal_entry->content);
        $this->assertFalse(
            array_key_exists('secret', $journal_entry->content)
        );
    }
}
