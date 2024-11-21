pipeline {
    agent any
        stages {
            stage('fix_new-1') {
                        when {
            		    branch 'fix_new'
            		          }
                            steps {
                                sh 'ansible-playbook /home/jenkins/fix_new.yml -l hemis.fix_new'
                            }
                        }
            stage('demo') {
                        when {
						  anyOf {
							branch 'master';
							branch 'MR-*'
								}
							}
                            steps {
                                sh 'ansible-playbook /home/jenkins/demo.yml -l demo'
                            }
                        }
            stage('update') {
                        when {
						  anyOf {
							branch 'master';
							branch 'MR-*'
								}	
							}
                            steps {
                                sh 'ansible-playbook /home/jenkins/otm.yml -l hemis1'
                            }
                        }
            stage('otm') {
                         when {
						  anyOf {
							branch 'master';
							branch 'MR-*'
								}
							}
                            steps {
                                 sh 'ansible-playbook /home/jenkins/hemis.yml -l hemis'	 
                                }
                            }
    }

}
