<?php

declare(strict_types=1);

namespace dreammiu;

use Exception;
use ZM\Annotation\Framework\Init;
use ZM\Annotation\OneBot\BotCommand;
use ZM\Annotation\OneBot\CommandArgument;
use ZM\Exception\OneBot12Exception;
use ZM\Middleware\TimerMiddleware;
use ZM\Utils\ZMRequest;

class ZMChatGPT
{
  /**
   * 申请方法打开 https://www.toptal.com/developers/hastebin/documentation
   * 点击使用github登陆就能获取一个了
   * ChatGPTKey可以去bilibili.com搜索相关教程
   */
  #[Init()]
  public function init(): void
  {
    // 初始化配置文件
    if (config('ZMChatGPT') === null) {
      logger()->notice('第一次使用ZMChatGPT需要去config/ZMChatGPT.json配置密钥');
      file_put_contents(WORKING_DIR . '/config/ZMChatGPT.json', json_encode(['HasteBinKey' => '', 'ChatGPTKey' => ''], JSON_PRETTY_PRINT));
    }
  }

  #[\BotCommand(match: '#清除个人设置')]
  public function testSegment(\BotContext $ctx)
  {
    $UserId = bot()->getEvent()->getUserId();
    kv('ZMChatGPTkv')->delete($UserId);
    $ctx->reply("你已手动重置个人设置");
  }

  /**
   * @throws OneBot12Exception
   * @throws Exception
   */
  #[\Middleware(TimerMiddleware::class)]
  #[BotCommand(match: "#")]
  #[BotCommand(start_with: "#")]
  #[CommandArgument(name: 'content', type: 'string', required: true, prompt: '请输入你要问的内容')]
  public function OpenAI(\BotContext $ctx): void
  {
    if (config('ZMChatGPT.HasteBinKey', '') === '' || config('ZMChatGPT.ChatGPTKey', '') === '') {
      $ctx->reply('你还没有配置插件:ZMChatGPT 所需Key，请先到(https://www.toptal.com/developers/hastebin/documentation) \n GPT的Key自行解决生成并配置该配置项（config/ZMChatGPT.json）');
      return;
    }
    $UserId = bot()->getEvent()->getUserId();
    $UserName = bot()->getEvent()->get('user_name');
    if (kv('ZMChatGPTkv')->has($UserId) == null) {
      //按每个人单独设置-给机器人设置一个名字
      $TempApiName = $ctx->prompt("第一次使用请为机器人取一个名字！", 120, "你是不是不想给我一个好听的名字！超时了。", ZM_PROMPT_MENTION_USER | ZM_PROMPT_TIMEOUT_MENTION_USER);
      $data = [
        ['role' => 'system', 'content' => '你是' . $TempApiName[0]->data['text'] . '！每句话前面加上你名字'],
        ['role' => 'user', 'content' => '我的名字叫' . $UserName . ',请在有我名字的地方加上"宝！"!'],
        ['role' => 'assistant', 'content' => "好的！你的名字是" . $UserName . "!我的名字是" . $TempApiName[0]->data['text']],
      ];
      kv('ZMChatGPTkv')->set($UserId, $data);
    }
    $prompt = $ctx->getParamString('content');
    $data = kv('ZMChatGPTkv')->get($UserId);
    $data[] = ['role' => 'user', 'content' => $prompt];
    kv('ZMChatGPTkv')->set($UserId, $data);

    dump(kv('ZMChatGPTkv')->get($UserId, $data));
    $api_key = config('ZMChatGPT.ChatGPTKey');
    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
    curl_setopt($ch, CURLOPT_PROXY, "10.31.1.94"); //代理服务器地址
    curl_setopt($ch, CURLOPT_PROXYPORT, 1081); //代理服务器端口
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP); //使用http代理模式
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json",
      "Authorization: Bearer " . $api_key,
    ]);
    $request_data = '{
    "model": "gpt-3.5-turbo",
    "messages": ' . json_encode($data) . '}';
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request_data);
    $result = curl_exec($ch);
    if ($result === false) {
      bot()->reply("ERROR: " . curl_error($ch));
    }
    $response = json_decode($result, true);
    //判断openai是否返回error

    dump($response);//打印获取api内容
    $answer = $response["choices"][0]["message"]["content"];
    $str = str_replace("\n", "", $answer);
    //如果不需要把if删除留bot()->reply(xx);即可xx可使用$str是返回内容中去除\n，$answer保留\n
    //这是判断GPT回复太长文本大于等于300则上传至网页！
    if ($response['usage']['completion_tokens'] >= 300) {
      $hastebinres = ZMRequest::post("https://hastebin.com/documents", header: [
        "Authorization" => "Bearer " . config('ZMChatGPT.HasteBinKey'),
        "Content-Type" => "text/plain",
        "User-Agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4472.124 Safari/537.36",
      ], data: "'" . $answer . "'");
      $responseObj = json_decode($hastebinres);
      bot()->reply("因为QQ限制长文本则自动上传至:https://hastebin.com/share/" . $responseObj->key);
    } else {
      bot()->reply($answer);
    }
    $data = kv('ZMChatGPTkv')->get($UserId);
    $data[] = ['role' => 'assistant', 'content' => $answer];
//因为接口api限制4096 tokens ，在3500 tokens左右的时候执行一次清除但是保留用户信息
    if ($response['usage']['total_tokens'] >= 3500) {
      $ResetBotName = kv('ZMChatGPTkv')->get($UserId);
      kv('ZMChatGPTkv')->delete($UserId);
      $ctx->reply("你的total_tokens已经清零，将自动保留用户信息，但上下文已经清空");
      sleep(3);
      $data = [
        $ResetBotName[0],
        $ResetBotName[1],
        $ResetBotName[2],
      ];
    };
    kv('ZMChatGPTkv')->set($UserId, $data);//最后把信息保存方便上下文对话
  }
}

