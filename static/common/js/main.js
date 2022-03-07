klsf = {
    ajax: function (url, data, success, fail, showLoad) {
        var load;
        if (showLoad !== false) {
            load = layer.open({
                type: 2, shadeClose: false
            });
        }
        jQuery.ajax({
            url: url,
            data: data,
            type: (data === null || data === undefined) ? 'get' : 'post',
            cache: false,
            dataType: 'json',
            success: function (data) {
                load !== undefined && layer.close(load);
                if (typeof (success) === 'function') {
                    success(data)
                }
            },
            error: function (data) {
                load !== undefined && layer.close(load);
                if (typeof (fail) === 'function') {
                    fail(data)
                } else {
                    layer.open({
                        content: '网络链接错误'
                        , skin: 'msg'
                        , time: 1
                    });
                }
            }
        })
    },
    msg: function (msg, icon, time) {
        $.toast({
            text: '',
            position: 'top-right',
            heading: msg,
            icon: (icon === undefined) ? 'info' : icon,
            showHideTransition: 'fade'
        })
    },
    getOrderStatus: function (status) {
        switch (parseInt(status)) {
            case 0:
                return '<span class="btn-sm btn-info">等待中</span>';
            case 1:
                return '<span class="btn-sm btn-primary">进行中</span>';
            case 2:
                return '<span class="btn-sm btn-warning">已退单</span>';
            case 3:
                return '<span class="btn-sm btn-danger">有异常</span>';
            case 4:
                return '<span class="btn-sm btn-warning">退款中</span>';
            case 5:
                return '<span class="btn-sm btn-success">已退款</span>';
            case 90:
                return '<span class="btn-sm btn-success">已完成</span>';
            default:
                return '<span class="btn-sm btn-danger">未○知</span>';
        }
    },
    url: function (model, action) {
        return '/index.php/index/' + model + '_ajax/' + action + '/'
    }
}