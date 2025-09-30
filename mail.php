<!doctype html>
<html lang="zh-CN">

<head>
    <meta charset="utf-8">
    <title>AJAX 邮件发送</title>
    <style>
        button {
            padding: 0.5em 1em;
            font-size: 1rem;
            cursor: pointer;
        }

        #result {
            margin-top: 1em;
            color: green;
        }
    </style>
</head>

<body>
    <h1>点击按钮发送邮件</h1>
    <button id="sendBtn">发送邮件</button>
    <p id="result"></p>

    <script>
        document.getElementById("sendBtn").addEventListener("click", function() {
            fetch("mail.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "action=send"
                })
                .then(response => response.json())
                .then(data => {
                    const result = document.getElementById("result");
                    if (data.success) {
                        result.style.color = "green";
                        result.textContent = data.message;
                    } else {
                        result.style.color = "red";
                        result.textContent = data.message;
                    }
                })
                .catch(err => {
                    document.getElementById("result").style.color = "red";
                    document.getElementById("result").textContent = "请求失败：" + err;
                });
        });
    </script>
</body>

</html>