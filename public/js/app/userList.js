/**
 * Created by CPR007 on 2017/10/27.
 */
myApp.controller('userList',function($scope,$http,$compile){
    $scope.selInfo={
        name:'',
        page:1
    };
    $scope.getDataUrl='/api/user/getUserList';
    $scope.selData=function(mac,mobile,username,nickname){
        $scope.selInfo.name=nickname;
        //重置页数
        $scope.selInfo.page=1;
        $scope.getData();
    };

    $scope.getData=function(){
        $http({method:"post",url:$scope.getDataUrl,data:$scope.selInfo}).success(function (data) {
            console.log(data);
            var html=data.data.page;
            html=$compile(html)($scope);
            $scope.list=data.data.list;
            $(".page").html(html);
        });

    };

    $scope.changePage=function(page){
        $scope.selInfo.page=page;
        $scope.getData();
    };
    //初始化数据
    $scope.getData();
});