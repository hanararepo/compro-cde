<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ContactUsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_public_user_can_view_contact_page(): void
    {
        $response = $this->get(route('contact'));

        $response->assertOk();
        $response->assertSee('Contact Us');
    }

    public function test_public_user_can_submit_contact_form(): void
    {
        $payload = [
            'recaptcha_token' => 'test-token-skipped-in-testing-env',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Inquiry about Services',
            'message' => 'Hello, I would like to know more about your CMS services.',
        ];

        $response = $this->post(route('contact.store'), $payload);

        $response->assertRedirect(route('contact'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Inquiry about Services',
            'is_read' => false,
        ]);
    }

    public function test_contact_form_validation_requires_all_fields(): void
    {
        $response = $this->post(route('contact.store'), []);

        $response->assertSessionHasErrors(['recaptcha_token', 'name', 'email', 'subject', 'message']);
        $this->assertEquals(0, ContactMessage::count());
    }

    public function test_contact_form_blocked_when_recaptcha_token_missing(): void
    {
        $response = $this->post(route('contact.store'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message body.',
            // recaptcha_token intentionally omitted
        ]);

        $response->assertSessionHasErrors('recaptcha_token');
        $this->assertEquals(0, ContactMessage::count());
    }

    public function test_contact_form_rate_limiting_prevents_brute_force(): void
    {
        $payload = [
            'recaptcha_token' => 'test-token-skipped-in-testing-env',
            'name' => 'Spammer',
            'email' => 'spam@example.com',
            'subject' => 'Spam Subject',
            'message' => 'Spam content here',
        ];

        // 5 allowed requests per minute
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('contact.store'), $payload)->assertStatus(302);
        }

        // 6th request within the same minute should be throttled
        $response = $this->post(route('contact.store'), $payload);
        $response->assertStatus(429);
    }

    public function test_admin_can_view_contact_messages_list_and_details(): void
    {
        $admin = User::where('email', 'admin@cms.local')->first();

        $message = ContactMessage::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'subject' => 'Collaboration Request',
            'message' => 'We are interested in collaborating with you.',
            'is_read' => false,
        ]);

        $this->actingAs($admin);

        // View list
        $response = $this->get(route('admin.contact-messages.index'));
        $response->assertOk();
        $response->assertSee('Jane Smith');
        $response->assertSee('Collaboration Request');

        // View details — should mark message as read
        $showResponse = $this->get(route('admin.contact-messages.show', $message));
        $showResponse->assertOk();
        $showResponse->assertSee('Collaboration Request');
        $showResponse->assertSee('mailto:jane@example.com', false);

        $this->assertTrue($message->fresh()->is_read);
    }

    public function test_admin_can_delete_contact_message(): void
    {
        $admin = User::where('email', 'admin@cms.local')->first();

        $message = ContactMessage::create([
            'name' => 'Delete Me',
            'email' => 'delete@example.com',
            'subject' => 'To be deleted',
            'message' => 'Please delete this test message.',
        ]);

        $this->actingAs($admin);

        $response = $this->delete(route('admin.contact-messages.destroy', $message));

        $response->assertRedirect(route('admin.contact-messages.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('contact_messages', [
            'id' => $message->id,
        ]);
    }

    public function test_unauthorized_user_cannot_access_contact_messages(): void
    {
        $author = User::where('email', 'author@cms.local')->first();

        $message = ContactMessage::create([
            'name' => 'Secret',
            'email' => 'secret@example.com',
            'subject' => 'Secret message',
            'message' => 'Should not be seen by author.',
        ]);

        $this->actingAs($author);

        $this->get(route('admin.contact-messages.index'))->assertForbidden();
        $this->get(route('admin.contact-messages.show', $message))->assertForbidden();
        $this->delete(route('admin.contact-messages.destroy', $message))->assertForbidden();
    }

    public function test_author_dashboard_does_not_see_contact_messages(): void
    {
        $author = User::where('email', 'author@cms.local')->first();

        ContactMessage::create([
            'name' => 'Test Inquirer',
            'email' => 'inquirer@example.com',
            'subject' => 'Help needed',
            'message' => 'Need some help with the system.',
            'is_read' => false,
        ]);

        $response = $this->actingAs($author)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertDontSee('Recent Inquiries');
    }

    public function test_admin_dashboard_sees_contact_messages_and_recent_inquiries(): void
    {
        $admin = User::where('email', 'admin@cms.local')->first();

        ContactMessage::create([
            'name' => 'Test Inquirer',
            'email' => 'inquirer@example.com',
            'subject' => 'Help needed',
            'message' => 'Need some help with the system.',
            'is_read' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Recent Inquiries');
        $response->assertSee('Help needed');
    }
}
