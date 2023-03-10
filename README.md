# ZMChatGPT

这是一个 zhamao-robot / zhamao-framework 的插件，他的作用是请求gpt3.5的api


## 安装

```bash
./zhamao plugin:install https://github.com/awesome-zhamao/hitokoto.git
```

## 命令
| 触发指令 | 介绍 |
| --- | --- |
|  # | 使用#+内容 向机器人提问 |
| #清除个人设置 | 清除个人设置（包括向GPT提问的上下文，给机器人的名字） |

###因为GPT接口限制插件上下文total_tokens在3700左右会自动重置上下文信息并且保留第一次设置机器人名称，如需重置个人设定 使用 “#清除个人设置” 命令清除

比如例如下方图片
![2{QIB0DMKIR0B@7OP 2`A}P](https://user-images.githubusercontent.com/30835281/224357757-c7db810e-6959-4ae9-8987-eba6af201bd9.jpg)
