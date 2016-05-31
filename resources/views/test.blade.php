<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
<form action="">
    <label for="answer">Answer</label>
    <input type="text" v-model="answer">
    <input type="submit" value="submit" @click.prevent="submit">
    <input type="submit" value="refresh" @click.prevent="refresh">
</form>

<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/1.0.24/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue-resource/0.7.2/vue-resource.min.js"></script>
<script>
    new Vue({
        el: "body",
        http:{
            headers:{
                "ana-myCaptcha-token":"D3EYVvAPcAirbhTxl8GT5PMRfxryp8lcA7g6"
            }
        },
        data: {
            captchaId: "",
            answer: ""
        },
        methods: {
            submit: function () {
                var url = "http://mycaptcha.anacreation.com/api/captcha?captchaId=" + this.captchaId;
                this.$http.post(url, {answer:this.answer}).then(function (response) {
                    console.log(response)
                })
            },
            refresh: function () {
                var self = this;
                var url = "http://mycaptcha.anacreation.com/api/captcha";
                this.$http.get(url).then(function (response) {
                    console.log(response);
                    var img = document.getElementById("captchaImage");
                    img.src = response.data.imageUrl;
                    self.captchaId = response.data.captchaId;
                })
            }
        },
        ready: function () {
            var self = this;
            var url = "http://mycaptcha.anacreation.com/api/captcha";
            this.$http.get(url).then(function (response) {
                console.log(response);
                var img = document.createElement("img");
                img.src = response.data.imageUrl;
                img.id = "captchaImage";
                self.captchaId = response.data.captchaId;
                document.querySelector("form").appendChild(img)
            })
        }
    })
</script>
</body>

</html>