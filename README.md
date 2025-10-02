# SupportCandy Queues Macro

Adds a real-time queue count macro to SupportCandy emails, allowing you to show customers how many tickets are ahead of them.

## Description

This WordPress plugin extends the functionality of SupportCandy by adding a new email macro: `{{queue_count}}`. When used in an email template, this macro is dynamically replaced with the number of open tickets of the same type (e.g., category, priority) as the ticket that triggered the email.

This is useful for managing customer expectations by giving them an idea of the current support load for their specific issue.

## Features

*   **Real-time Queue Count:** Automatically calculates and displays the number of open tickets in the same queue.
*   **Configurable Statuses:** You can define which ticket statuses are considered "non-closed" from the settings page, giving you full control over what's included in the count.
*   **Customizable Ticket Type:** By default, the queue is determined by the ticket's `category`. You can change this to any other relevant field in your ticket table.
*   **Easy to Use:** Simply add the `{{queue_count}}` macro to your SupportCandy email templates.

## Installation

1.  Download the plugin files.
2.  Upload the `WP-SupportCandy-Queue-Macro` directory to your `/wp-content/plugins/` directory.
3.  Activate the plugin through the 'Plugins' menu in WordPress.

## Usage

1.  Navigate to **SupportCandy Queues** from the main WordPress admin menu.
2.  In the settings page, select the ticket statuses that should be counted as part of the queue. Move them from the "Available" list to the "Selected" list.
3.  (Optional) If you use a field other than `category` to distinguish between ticket types, enter that field's name in the "Ticket Type Field" input box.
4.  Click **Save Settings**.
5.  Go to your SupportCandy email templates and insert the `{{queue_count}}` macro where you want the queue count to appear.

Now, when an email is sent for a new ticket, the macro will be replaced with the current queue count for that ticket's type.

## To-Do

*   Improve security by validating the ticket type field against a whitelist.
*   Add an uninstallation hook to clean up database options.
*   Enhance internationalization.