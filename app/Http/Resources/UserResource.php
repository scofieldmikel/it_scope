<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\StatusResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    protected $sanStatus;

    public function __construct($resource, bool $sanStatus = false)
    {
        $this->sanStatus = $sanStatus;
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'user_type' => $this->user_type,
            'status' => $this->status->name,
            'verified' => ! is_null($this->email_verified_at),
            'date_joined' => $this->created_at->format('F jS Y'),
            $this->mergeWhen($this->sanStatus, [
                'token' => $this->createToken('API Token')->plainTextToken,
            ]),
        ];
    }
}
