<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Language $english;
    protected Language $arabic;

    protected function setUp(): void
    {
        parent::setUp();

        $this->english = Language::updateOrCreate(['code' => 'en'], [
            'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr',
            'is_active' => true, 'is_default' => true,
        ]);
        $this->arabic = Language::updateOrCreate(['code' => 'ar'], [
            'name' => 'Arabic', 'native_name' => 'العربية', 'direction' => 'rtl',
            'is_active' => true, 'is_default' => false,
        ]);

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    public function translations_manager_index_is_accessible_by_admin(): void
    {
        $this->actingAs($this->admin)
            ->get('/translations-manager')
            ->assertOk();
    }

    /** @test */
    public function translations_manager_requires_authentication(): void
    {
        $this->get('/translations-manager')
            ->assertRedirect('/login');
    }

    /** @test */
    public function admin_can_create_a_new_translation_key_with_values(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/translations-manager', [
                'key'          => 'test_group.my_new_key',
                'group'        => 'test_group',
                'description'  => 'A test key for CRUD verification',
                'translations' => [
                    $this->english->id => 'My New Value',
                    $this->arabic->id  => 'قيمتي الجديدة',
                ],
            ]);

        // Accept redirect or JSON 200/201
        $response->assertStatus($response->isRedirect() ? 302 : 200);

        $this->assertDatabaseHas('translation_keys', [
            'key'   => 'test_group.my_new_key',
            'group' => 'test_group',
        ]);

        $key = TranslationKey::where('key', 'test_group.my_new_key')->first();
        $this->assertNotNull($key);

        $this->assertDatabaseHas('ui_translations', [
            'translation_key_id' => $key->id,
            'language_id'        => $this->english->id,
            'value'              => 'My New Value',
        ]);

        $this->assertDatabaseHas('ui_translations', [
            'translation_key_id' => $key->id,
            'language_id'        => $this->arabic->id,
            'value'              => 'قيمتي الجديدة',
        ]);
    }

    /** @test */
    public function admin_can_update_a_translation_value_inline(): void
    {
        $key = TranslationKey::create([
            'key'   => 'test.editable_key',
            'group' => 'test',
        ]);

        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id'        => $this->english->id,
            'value'              => 'Original Value',
        ]);

        $response = $this->actingAs($this->admin)
            ->put('/translations-manager/update-inline', [
                'translation_key_id' => $key->id,
                'language_id'        => $this->english->id,
                'value'              => 'Updated Value',
            ]);

        $response->assertStatus($response->isRedirect() ? 302 : 200);

        $this->assertDatabaseHas('ui_translations', [
            'translation_key_id' => $key->id,
            'language_id'        => $this->english->id,
            'value'              => 'Updated Value',
        ]);
    }

    /** @test */
    public function admin_can_delete_a_translation_key(): void
    {
        $key = TranslationKey::create([
            'key'   => 'test.deletable_key',
            'group' => 'test',
        ]);

        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id'        => $this->english->id,
            'value'              => 'Will be deleted',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/translations-manager/{$key->id}");

        $response->assertStatus($response->isRedirect() ? 302 : 200);

        $this->assertDatabaseMissing('translation_keys', ['id' => $key->id]);
        // Cascade delete should remove ui_translations too
        $this->assertDatabaseMissing('ui_translations', ['translation_key_id' => $key->id]);
    }

    /** @test */
    public function translations_manager_index_shows_existing_keys(): void
    {
        TranslationKey::create(['key' => 'test.visible_key', 'group' => 'test']);
        TranslationKey::create(['key' => 'test.another_key', 'group' => 'test']);

        $response = $this->actingAs($this->admin)
            ->get('/translations-manager');

        $response->assertOk();
        // The page should include keys in the response
        $this->assertStringContainsString('test.visible_key', $response->getContent());
    }

    /** @test */
    public function translations_manager_supports_search_filter(): void
    {
        TranslationKey::create(['key' => 'search.unique_needle', 'group' => 'search']);
        TranslationKey::create(['key' => 'search.other_key', 'group' => 'search']);

        $response = $this->actingAs($this->admin)
            ->get('/translations-manager?search=unique_needle');

        $response->assertOk();
        $this->assertStringContainsString('unique_needle', $response->getContent());
    }

    /** @test */
    public function deleting_translation_key_cascades_to_ui_translations(): void
    {
        $key = TranslationKey::create(['key' => 'cascade.test_key', 'group' => 'cascade']);

        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id'        => $this->english->id,
            'value'              => 'cascade value',
        ]);
        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id'        => $this->arabic->id,
            'value'              => 'cascade ar value',
        ]);

        $this->assertEquals(2, UiTranslation::where('translation_key_id', $key->id)->count());

        $key->delete();

        $this->assertEquals(0, UiTranslation::where('translation_key_id', $key->id)->count(),
            'Deleting a TranslationKey must cascade-delete its UiTranslation rows');
    }
}
