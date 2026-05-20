<?php

function notify_send(string $to, string $subject, string $body): bool {
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $sent = send_app_mail($to, $subject, $body, SITE_EMAIL);
    if (!$sent) {
        error_log('Email notification failed for ' . $to . ': ' . smtp_last_error());
    }

    return $sent;
}

function notify_admin(string $subject, string $body): bool {
    return notify_send((string) NOTIFICATION_EMAIL, $subject, $body);
}

function notify_label(string $value): string {
    return ucwords(str_replace('_', ' ', $value));
}

function notify_contact_message(array $form): void {
    $admin_body = "New contact message received.\n\n"
        . "Name: {$form['name']}\n"
        . "Email: {$form['email']}\n"
        . "Phone: " . ($form['phone'] !== '' ? $form['phone'] : 'Not provided') . "\n"
        . "Subject: {$form['subject']}\n\n"
        . "Message:\n{$form['message']}\n\n"
        . "Open admin messages:\n" . SITE_URL . "/public/admin/messages.php";

    notify_admin('New contact message - ' . $form['subject'], $admin_body);

    $customer_body = "Hello {$form['name']},\n\n"
        . "We have received your message and will follow up with the best next step.\n\n"
        . "Subject: {$form['subject']}\n\n"
        . "Your message:\n{$form['message']}\n\n"
        . SITE_NAME;

    notify_send($form['email'], 'We received your message', $customer_body);
}

function notify_service_request(array $form, string $request_ref): void {
    $track_url = SITE_URL . '/public/request-status.php?ref=' . urlencode($request_ref);
    $admin_body = "New service request received.\n\n"
        . "Reference: {$request_ref}\n"
        . "Name: {$form['full_name']}\n"
        . "Email: {$form['email']}\n"
        . "Phone: " . ($form['phone'] !== '' ? $form['phone'] : 'Not provided') . "\n"
        . "Company: " . ($form['company_name'] !== '' ? $form['company_name'] : 'Not provided') . "\n"
        . "Project: " . ($form['project_title'] !== '' ? $form['project_title'] : 'Not provided') . "\n\n"
        . "Description:\n{$form['description']}\n\n"
        . "Open admin requests:\n" . SITE_URL . "/public/admin/requests.php";

    notify_admin('New service request - ' . $request_ref, $admin_body);

    $customer_body = "Hello {$form['full_name']},\n\n"
        . "Your service request has been received.\n\n"
        . "Reference: {$request_ref}\n"
        . "Track it here: {$track_url}\n\n"
        . "We will review the details and follow up with the clearest next step.\n\n"
        . SITE_NAME;

    notify_send($form['email'], 'Service request received - ' . $request_ref, $customer_body);
}

function notify_repair_request(array $form, string $repair_ref): void {
    $track_url = SITE_URL . '/public/repair-status.php?ref=' . urlencode($repair_ref);
    $device = trim($form['brand'] . ' ' . $form['model']);
    $admin_body = "New repair booking received.\n\n"
        . "Reference: {$repair_ref}\n"
        . "Name: {$form['customer_name']}\n"
        . "Email: " . ($form['customer_email'] !== '' ? $form['customer_email'] : 'Not provided') . "\n"
        . "Phone: {$form['customer_phone']}\n"
        . "Device: {$form['device_type']}" . ($device !== '' ? " - {$device}" : '') . "\n"
        . "Serial: " . ($form['serial_number'] !== '' ? $form['serial_number'] : 'Not provided') . "\n\n"
        . "Issue:\n{$form['issue_description']}\n\n"
        . "Open admin repairs:\n" . SITE_URL . "/public/admin/repairs.php";

    notify_admin('New repair booking - ' . $repair_ref, $admin_body);

    if ($form['customer_email'] !== '') {
        $customer_body = "Hello {$form['customer_name']},\n\n"
            . "Your repair request has been received.\n\n"
            . "Reference: {$repair_ref}\n"
            . "Track it here: {$track_url}\n\n"
            . "We will contact you about intake, diagnosis, and next steps.\n\n"
            . SITE_NAME;

        notify_send($form['customer_email'], 'Repair request received - ' . $repair_ref, $customer_body);
    }
}

function notify_order_created(array $form, array $items, string $order_ref, float $total): void {
    $lines = [];
    foreach ($items as $item) {
        $lines[] = '- ' . $item['product']['name'] . ' x' . $item['qty'] . ' = ' . money((float) $item['line_total']);
    }

    $summary = implode("\n", $lines);
    $admin_body = "New order placed.\n\n"
        . "Reference: {$order_ref}\n"
        . "Name: {$form['full_name']}\n"
        . "Email: {$form['email']}\n"
        . "Phone: {$form['phone']}\n"
        . "Payment: " . notify_label($form['payment_method']) . "\n"
        . "Total: " . money($total) . "\n\n"
        . "Items:\n{$summary}\n\n"
        . "Delivery:\n{$form['delivery_address']}\n{$form['city']}, {$form['country']}\n\n"
        . "Open admin orders:\n" . SITE_URL . "/public/admin/orders.php";

    notify_admin('New order - ' . $order_ref, $admin_body);

    $customer_body = "Hello {$form['full_name']},\n\n"
        . "Your order has been received.\n\n"
        . "Reference: {$order_ref}\n"
        . "Total: " . money($total) . "\n\n"
        . "Items:\n{$summary}\n\n"
        . "We will contact you to confirm payment and delivery details.\n\n"
        . SITE_NAME;

    notify_send($form['email'], 'Order received - ' . $order_ref, $customer_body);
}

function notify_order_status(array $order): void {
    $body = "Hello {$order['full_name']},\n\n"
        . "Your order has been updated.\n\n"
        . "Reference: {$order['order_ref']}\n"
        . "Delivery status: " . notify_label((string) $order['delivery_status']) . "\n"
        . "Payment status: " . notify_label((string) $order['payment_status']) . "\n\n"
        . "If you have questions, reply to this email or contact Joetech.\n\n"
        . SITE_NAME;

    notify_send((string) $order['email'], 'Order update - ' . $order['order_ref'], $body);
}

function notify_service_status(array $request): void {
    $track_url = SITE_URL . '/public/request-status.php?ref=' . urlencode((string) $request['request_ref']);
    $body = "Hello {$request['full_name']},\n\n"
        . "Your service request has been updated.\n\n"
        . "Reference: {$request['request_ref']}\n"
        . "Status: " . notify_label((string) $request['status']) . "\n"
        . "Track it here: {$track_url}\n\n"
        . SITE_NAME;

    notify_send((string) $request['email'], 'Service request update - ' . $request['request_ref'], $body);
}

function notify_repair_status(array $repair): void {
    if (empty($repair['customer_email'])) {
        return;
    }

    $track_url = SITE_URL . '/public/repair-status.php?ref=' . urlencode((string) $repair['repair_ref']);
    $body = "Hello {$repair['customer_name']},\n\n"
        . "Your repair job has been updated.\n\n"
        . "Reference: {$repair['repair_ref']}\n"
        . "Status: " . notify_label((string) $repair['repair_status']) . "\n";

    if (!empty($repair['diagnosis'])) {
        $body .= "Diagnosis: {$repair['diagnosis']}\n";
    }

    if ($repair['estimated_cost'] !== null && $repair['estimated_cost'] !== '') {
        $body .= "Estimated cost: " . money((float) $repair['estimated_cost']) . "\n";
    }

    $body .= "\nTrack it here: {$track_url}\n\n" . SITE_NAME;

    notify_send((string) $repair['customer_email'], 'Repair update - ' . $repair['repair_ref'], $body);
}

function notify_blog_comment(array $post, array $comment): void {
    $body = "New blog comment awaiting moderation.\n\n"
        . "Post: {$post['title']}\n"
        . "Name: {$comment['guest_name']}\n"
        . "Email: {$comment['guest_email']}\n\n"
        . "Comment:\n{$comment['content']}\n\n"
        . "Open comments:\n" . SITE_URL . "/public/admin/blog-comments.php";

    notify_admin('New blog comment awaiting moderation', $body);
}
