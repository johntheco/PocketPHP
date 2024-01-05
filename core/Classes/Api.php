<?php


class Api
{
	public static function JsonResponse($data) {
		die(json_encode($data));
	}

	public static function SuccessResponse($message, $data = null) {
		(is_null($data))
			? die(json_encode(["status" => "Success", "message" => $message]))
			: die(json_encode(["status" => "Success", "message" => $message, "data" => $data]));
	}

	public static function FailureResponse($message, $data = null) {
		(is_null($data))
			? die(json_encode(["status" => "Failure", "message" => $message]))
			: die(json_encode(["status" => "Failure", "message" => $message, "data" => $data]));
	}
}
