# ZMChatGPT

这是一个 zhamao-robot / zhamao-framework 的插件，他的作用是请求gpt3.5的api
### 框架适配
##### 基于框架: 3.1.9 开发
## 安装

```bash
./zhamao plugin:install https://github.com/dreammiu/zm-chat-gpt.git
```

## 使用

1.安装完成后启动一次炸毛生成配置文(在zhamao根目录../zhamao-v3/config/ZMChatGPT.json)

```config
{
  "HasteBinKey": "366f56d000000000000000000000000000000000e12539000000000003",
  "ChatGPTKey": "sb-0000000000000000000000"
}
```

### HasteBinKey申请地址:

https://www.toptal.com/developers/hastebin/documentation

### ChatGPTKey申请地址:

注意！新版本默认使用openai-sb的接口，申请key相关可以在他们官网寻找默认使用:https://openai-sb.com/

注意！新版本默认使用openai-sb的接口，申请key相关可以在他们官网寻找默认使用:https://openai-sb.com/

注意！新版本默认使用openai-sb的接口，申请key相关可以在他们官网寻找默认使用:https://openai-sb.com/

### 修改默认ChatGP

1.打开在../zhamao-v3/vendor/dreammiu/zm-chat-gpt/src/ZMChatGPT.php
2.寻找“-sb”然后删除(在文件中约第71行)
3.可有可无，如果返回慢，需要配置代理

## 命令

| 触发指令    | 介绍                           |
|---------|------------------------------|
| #       | 使用#+内容 向机器人提问                |
| #清除个人设置 | 清除个人设置（包括向GPT提问的上下文，给机器人的名字） |

##因为GPT接口限制插件上下文total_tokens在3700左右会自动重置上下文信息并且保留第一次设置机器人名称，如需重置个人设定 使用
“#清除个人设置” 命令清除
在curl修改代码中插入（约72行中插修改代码然后配置）

``` httpProRT
curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
curl_setopt($ch, CURLOPT_PROXY, "127.0.0.1"); //代理服务器地址
curl_setopt($ch, CURLOPT_PROXYPORT, 1081); //代理服务器端口
curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP); //使用http代理模式
```
比如例如下方图片
![2{QIB0DMKIR0B@7OP 2`A}P](https://user-images.githubusercontent.com/30835281/224357757-c7db810e-6959-4ae9-8987-eba6af201bd9.jpg)
