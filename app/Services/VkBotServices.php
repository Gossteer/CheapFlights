<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VkBotServices
{
    private static array $func;
    private static array $func_message;
    private static array $func_search_ticket;
    private array $request_vk;

    private UserData $user_data;

    const FUNK = [
        'search_tickets' => 'search_tickets',
        'start' => 'start',
        'subscribe_now' => 'subscribe_now',
        'healp' => 'healp',
        'error' => 'error',
        'back' => 'back'
    ];

    private VkApi2Services $vk_api_v2;

    public function __construct()
    {
        $this->vk_api_v2 = new VkApi2Services(config('vk.api.VK_GROUP_API_TOKEN'), '5.131');

        self::$func_search_ticket = $this->funcSearchTicket();
        self::$func_message = $this->setFuncMessage();
        self::$func = $this->setFunc();
    }

    private function funcSearchTicket(): array
    {
        return [
            0 => function (): string {
                return $this->messageSend($this->vk_api_v2->prepareMessageData(
                    [
                        'message' => 'Необходимо выдать доступ данной группе',
                        'peer_id' => $this->request_vk['object']['message']['peer_id'],
                        'attachment' => 'photo-206970444_457239017',
                    ],
                    $this->vk_api_v2->prepareKeyboard(false, false, [
                        [
                            'label' => 'Выдать доступ',
                            'link' => 'https://vk.com/public' . config('vk.groups.SEND_SUBSCRIPTION_SEARCH_VK_PUBLIC_ID', '205982619'),
                            'type' => 'open_link'
                        ],
                        [
                            [
                                'label' => 'Я выдал доступ',
                                'payload' => "{\"command\":\"" . self::FUNK['search_tickets'] . "\"}",
                                'type' => 'callback'
                            ],
                            [
                                'label' => 'Главная',
                                'payload' => "{\"command\":\"" . self::FUNK['healp'] . "\"}",
                                'type' => 'callback'
                            ]
                        ]
                    ])
                ));
            },
            1 => function (array $user_value): string {
                if (!$user_value[self::FUNK['search_tickets']]['send_respons']) {
                    $user_value[self::FUNK['search_tickets']]['send_respons'] = true;
                    $this->setUserValue($user_value);
                    $result = $this->messageSend($this->vk_api_v2->prepareMessageData(
                        [
                            'message' => 'Введите пожалуйста город отправления',
                            'peer_id' => $this->request_vk['object']['message']['peer_id'],
                        ],
                        $this->vk_api_v2->prepareKeyboard(false, false, [
                            [
                                'label' => 'Главная',
                                'payload' => "{\"command\":\"" . self::FUNK['healp'] . "\"}",
                                'type' => 'callback'
                            ]
                        ])
                    ));
                };

                if (!$user_value[self::FUNK['search_tickets']]['get_request']) {
                    $user_value[self::FUNK['search_tickets']]['get_request'] = true;
                    $user_value[self::FUNK['search_tickets']]['data']['city'] = $this->request_vk['object']['message']['text'];
                    $this->setUserValue($user_value);
                    $result = self::$func_message[self::FUNK['search_tickets']]();
                }

                return $result;
            },
            2 => function (array $user_value): string {
                if (!$user_value[self::FUNK['search_tickets']]['send_respons']) {
                    $user_value[self::FUNK['search_tickets']]['send_respons'] = true;
                    $this->setUserValue($user_value);
                    $result = $this->messageSend($this->vk_api_v2->prepareMessageData(
                        [
                            'message' => 'Введите пожалуйста город назначения',
                            'peer_id' => $this->request_vk['object']['message']['peer_id'],
                        ],
                        $this->vk_api_v2->prepareKeyboard(false, false, [
                            [
                                [
                                    'label' => 'Главная',
                                    'payload' => "{\"command\":\"" . self::FUNK['healp'] . "\"}",
                                    'type' => 'callback'
                                ],
                                [
                                    'label' => 'Назад',
                                    'payload' => "{\"command\":\"" . self::FUNK['back'] . "\"}",
                                    'type' => 'callback'
                                ]
                            ]
                        ])
                    ));
                }
                if (!$user_value[self::FUNK['search_tickets']]['get_request']) {
                    $user_value[self::FUNK['search_tickets']]['get_request'] = true;
                    $user_value[self::FUNK['search_tickets']]['data']['city2'] = $this->request_vk['object']['message']['text'];
                    $this->setUserValue($user_value);
                    $result = self::$func_message[self::FUNK['search_tickets']]();
                }

                return $result;
            },
            3 => function (array $user_value): string {
                $user_value[self::FUNK['search_tickets']]['step'] = 3;

                $this->setUserValue($user_value);

                return $this->messageSend($this->vk_api_v2->prepareMessageData(
                    [
                        'message' => 'Тест тест тест',
                        'peer_id' => $this->request_vk['object']['message']['peer_id'],
                    ],
                    $this->vk_api_v2->prepareKeyboard(false, false, [
                        [
                            [
                                'label' => 'Главная',
                                'payload' => "{\"command\":\"" . self::FUNK['healp'] . "\"}",
                                'type' => 'callback'
                            ],
                            [
                                'label' => 'Назад',
                                'payload' => "{\"command\":\"" . self::FUNK['back'] . "\"}",
                                'type' => 'callback'
                            ]
                        ]
                    ])
                ));
            },
        ];
    }

    private function setFunc(): array
    {
        return [
            'confirmation' => static function () {
                exit('85e3b31e');
            },
            'message_new' => function (): string {
                $func =
                    json_decode($this->request_vk['object']['message']['payload'] ?? '', true)['command']
                    ?? $this->getUserValue()['step']
                    ?? self::FUNK['error'];

                return self::$func_message[$func]();
            },
        ];
    }

    private function setFuncMessage(): array
    {
        return [
            self::FUNK['back'] => function (): string {
                $user_value = $this->getUserValue();
                $user_value[$user_value['step']]['step']--;
                $this->defaultFlags($user_value['step'], $user_value);
                $this->setUserValue($user_value);

                return self::$func_message[$user_value['step']]();
            },
            self::FUNK['start'] => function (): string {
                return $this->defaultMessageSend('Добро пожаловать', $this->request_vk['object']['message']['peer_id']);
            },
            self::FUNK['error'] => function (): string {
                return $this->defaultMessageSend('Я вас не понял', $this->request_vk['object']['message']['peer_id']);
            },
            self::FUNK['search_tickets'] => function (): string {
                $user_value = $this->getUserValue();

                if (!$this->chatAllowed($this->user_data->user_id, 205982619)) {
                    return self::$func_search_ticket[0]();
                }

                if (!isset($user_value[self::FUNK['search_tickets']])) {
                    $user_value[self::FUNK['search_tickets']] = [
                        'subscriptions' => [],
                        'step' => 1,
                        'send_respons' => false,
                        'get_request' => false,
                        'data' => []
                    ];

                    $this->setUserValue($user_value);
                }

                if ($user_value['step'] === null && $user_value[self::FUNK['search_tickets']]['step'] !== 1) {
                    $this->defaultMessageSend('Продолжим: ', $this->request_vk['object']['message']['peer_id']);
                }

                $user_value['step'] = self::FUNK['search_tickets'];

                if (
                    $user_value[self::FUNK['search_tickets']]['send_respons'] &&
                    $user_value[self::FUNK['search_tickets']]['get_request']
                ) {
                    $user_value[self::FUNK['search_tickets']]['step']++;
                    $this->defaultFlags(self::FUNK['search_tickets'], $user_value);
                }

                return self::$func_search_ticket[$user_value[self::FUNK['search_tickets']]['step']]($user_value);
            },
            self::FUNK['subscribe_now'] => function (): string {
                return $this->defaultMessageSend('Сейчас оформим', $this->request_vk['object']['message']['peer_id']);
            },
            self::FUNK['healp'] => function (): string {
                $user_value = $this->getUserValue();
                if (isset($user_value['step'])) {
                    $user_value[$user_value['step']]['step']--;
                    $this->defaultFlags($user_value['step'], $user_value);
                    $user_value['step'] = null;
                    $this->setUserValue($user_value);
                }

                return $this->defaultMessageSend('Я вам не помощник', $this->request_vk['object']['message']['peer_id']);
            }
        ];
    }

    private function defaultFlags(string $funk, array &$user_value): void
    {
        $user_value[$funk]['get_request'] = false;
        $user_value[$funk]['send_respons'] = false;
    }

    private function getUserValue(): ?array
    {
        return json_decode($this->user_data->value, true);
    }

    private function setUserValue(array $user_value): void
    {
        $this->user_data->value = json_encode($user_value);
    }

    public function main(Request $request): string
    {
        $this->log("request vk-bot", $this->request_vk = $request->all());

        try {
            $this->setUserData($this->request_vk['object']['message']['from_id'] ?? null);

            $this->log('response vk-bot', json_decode(self::$func[$this->request_vk['type']](), true) ?? []);

            $this->user_data->save();
        } catch (\Throwable $th) {
            $this->log("error vk-bot :\n{$th->getMessage()}\n{$th->getLine()}", $th->getTrace());
        }

        return 'OK';
    }

    private function setUserData(?int $from_id)
    {
        if ($from_id && ($user_data = UserData::where('user_id', $from_id)->where('key', 'bot')->first())) {
            $this->user_data = $user_data;
        } elseif ($from_id) {
            $this->user_data = new UserData();
            $this->user_data->user_id = $from_id;
            $this->user_data->key = 'bot';
            $this->user_data->value = '';
            $this->user_data->save();
        }
    }

    private function log(string $message, ?array $context = []): void
    {
        Log::info($message, $context);
    }

    private function chatAllowed(int $user_id, int $group_id): bool
    {
        return json_decode($this->groupAllowed($this->vk_api_v2->prepareMessageData(
            [
                'group_id' => $group_id,
                'user_id' => $user_id,
                'access_token' => Chat::findOrFail($group_id)->token
            ],
            $this->defaultKeyboard(),
        )), true)['response']['is_allowed'];
    }

    private function groupAllowed(array $params): string
    {
        return $this->vk_api_v2->call(
            $this->vk_api_v2->prepareUrl(
                'messages.isMessagesFromGroupAllowed',
                $params
            )
        )->getBody()->getContents();
    }

    private function defaultMessageSend(string $text, int $peer_id): string
    {
        return $this->messageSend($this->vk_api_v2->prepareMessageData(
            [
                'message' => $text,
                'peer_id' => $peer_id
            ],
            $this->defaultKeyboard()
        ));
    }

    private function messageSend(array $params): string
    {
        return $this->vk_api_v2->call(
            $this->vk_api_v2->prepareUrl(
                'messages.send',
                $params
            )
        )->getBody()->getContents();
    }

    private function defaultKeyboard(): string
    {
        return $this->vk_api_v2->prepareKeyboard(false, false, [
            [
                [
                    'label' => 'Оформить подписку',
                    'payload' => "{\"command\":\"" . self::FUNK['subscribe_now'] . "\"}",
                    'type' => 'callback'
                ],
                [
                    'label' => 'Поиск билетов',
                    'payload' => "{\"command\":\"" . self::FUNK['search_tickets'] . "\"}",
                    'type' => 'callback'
                ]
            ],
            [
                'label' => 'Помощь',
                'payload' => "{\"command\":\"" . self::FUNK['healp'] . "\"}",
                'type' => 'callback'
            ],
        ]);
    }
}
