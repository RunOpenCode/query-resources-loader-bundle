node {
    stage ('Provisioning') {
        git 'https://github.com/RunOpenCode/query-resources-loader-bundle'
    }
    docker.image('runopencode/php-testing-environment:7.0.15').inside {
        stage ('Build on 7.0.15') {
            sh 'ant'
        }
    }
    docker.image('runopencode/php-testing-environment:7.1.1').inside {
        stage ('Build on 7.1.1') {
            sh 'ant'
        }
    }
    stage('SonarQube') {
        def scannerHome = tool 'SonarQube Scanner 2.8';
        withSonarQubeEnv {
            sh "${scannerHome}/bin/sonar-scanner -Dsonar.projectKey=runopencode-query-resources-loader-bundle -Dsonar.projectName='Query resources loader - Symfony bundle' -Dsonar.projectVersion=1.0 -Dsonar.sources=src -Dsonar.language=php -Dsonar.sourceEncoding=UTF-8 -Dsonar.tests=test -Dsonar.php.tests.reportPath=build/logs/junit.xml -Dsonar.php.coverage.reportPath=build/logs/clover.xml -Dsonar.clover.reportPath=build/logs/clover.xml -Dsonar.coverage.exclusions=test"
        }
    }
    stage('Reporting') {
        step([$class: 'hudson.plugins.checkstyle.CheckStylePublisher', pattern: 'build/logs/checkstyle.xml'])
        step([$class: 'PmdPublisher', canComputeNew: false, pattern: 'build/logs/pmd.xml'])
        step([$class: 'DryPublisher', canComputeNew: false, defaultEncoding: '', healthy: '', pattern: 'build/logs/pmd-cpd.xml', unHealthy: ''])
        step([$class: 'JavadocArchiver', javadocDir: 'build/api', keepAll: false])
        step([$class: 'XUnitPublisher', testTimeMargin: '3000', thresholdMode: 1, thresholds: [[$class: 'FailedThreshold', failureNewThreshold: '', failureThreshold: '', unstableNewThreshold: '', unstableThreshold: ''], [$class: 'SkippedThreshold', failureNewThreshold: '', failureThreshold: '', unstableNewThreshold: '', unstableThreshold: '']], tools: [[$class: 'JUnitType', deleteOutputFiles: true, failIfNotNew: false, pattern: 'build/logs/junit.xml', skipNoTestFiles: false, stopProcessingIfError: true]]])
        publishHTML([allowMissing: false, alwaysLinkToLastBuild: false, keepAll: false, reportDir: 'build/coverage/html', reportFiles: 'index.html', reportName: 'Code coverage report'])
    }
}