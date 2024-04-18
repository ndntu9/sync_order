<? php

add_action('woocommerce_checkout_create_order', 'sync_to_smaxapp', 10, 2);

function sync_to_smaxapp($order, $data)
{

	$customer_data = array(
		'name' => $order->get_billing_last_name(),
		'phone' => $order->get_billing_phone()
	);


	$receiver_name = isset($_POST['text-1686544497686-0']) ? sanitize_text_field($_POST['text-1686544497686-0']) : '';
	$receiver_phone = isset($_POST['text-1686544511886-0']) ? sanitize_text_field($_POST['text-1686544511886-0']) : '';

	$receiver_data = array(
		'isActive' => true,
		'name' => $receiver_name,
		'phone' => $receiver_phone
	);

	$cart_data = array();

	$items_data = $order->get_items();

	foreach ($items_data as $item) {
		$item_id = $item->get_product_id();
		$item_quantity = $item->get_quantity();

		$api_url = "https://smax.app/api/public/bizs/hoa-tuoi-vung-tau/products/show?code=$item_id";

		$token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY2MDBlNDJkYTc0M2M0NmU4NzgzMjFhNCIsImFsaWFzIjoiaG9hLXR1b2ktdnVuZy10YXUiLCJlbnYiOiJwcm9kdWN0aW9uIiwiaWF0IjoxNzEyNjM3MDQwLCJleHAiOjE4NzAzMTcwNDB9.qDZxvy-cBD1tYj6oihR1lkJ-P1MibgZz9dTxn9ImBkQ';
		$headers = array(
			'Authorization: Bearer ' . $token,
			'Content-Type: application/json'
		);

		$options = array(
			'http' => array(
				'header' => $headers,
				'method' => 'GET'
			)
		);

		$context = stream_context_create($options);

		$response = file_get_contents($api_url, false, $context);

		$data = json_decode($response, true);

		$id = $data['data']['id'];
		$price = $data['data']['price'];

		$cart = array(
			'type' => 'PRODUCT',
			'product' => $id,
			'price' => $price,
			'quantity' => $item_quantity
		);

		array_push($cart_data, $cart);
	}

	$payload = array(
		'customer' => $customer_data,
		'receiver' => $receiver_data,
		'cart' => $cart_data
	);

	$api_url = 'https://smax.app/api/public/bizs/hoa-tuoi-vung-tau/sale-center/orders';

	$token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY2MDBlNDJkYTc0M2M0NmU4NzgzMjFhNCIsImFsaWFzIjoiaG9hLXR1b2ktdnVuZy10YXUiLCJlbnYiOiJwcm9kdWN0aW9uIiwiaWF0IjoxNzEyNjM3MDQwLCJleHAiOjE4NzAzMTcwNDB9.qDZxvy-cBD1tYj6oihR1lkJ-P1MibgZz9dTxn9ImBkQ';
	$headers = array(
		'Authorization: Bearer ' . $token,
		'Content-Type: application/json'
	);

	$options = array(
		'http' => array(
			'header' => $headers,
			'method' => 'POST',
			'content' => json_encode($payload)
		)
	);

	$context = stream_context_create($options);

	$response = file_get_contents($api_url, false, $context);
}
