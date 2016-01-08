angular.module("automateTemplate",['ngCsvImport','ngFileUpload','ui.bootstrap'])
.controller('MainCtrl', ['$scope', '$parse', '$http','$window','Upload','$sce',function ($scope, $parse, $http, $window, Upload, $sce) {
    $scope.currentProjectUrl = $sce.trustAsResourceUrl("https://www.youtube.com/embed/PnNwnSpT7-A");
}]);
