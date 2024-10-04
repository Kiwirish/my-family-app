# Cloud Shared Family Application

The **Cloud shared Family App** is a cloud-deployed web application that allows family members to communicate, vote on polls, and share family dreams. The application is built using PHP with the LAMP stack and integrates AWS services for server hosting, storage and notifications.

## Table of Contents

- [Cloud Shared Family Application](#cloud-shared-family-application)
  - [Table of Contents](#table-of-contents)
  - [Features](#features)
  - [Architecture Overview](#architecture-overview)
  - [Technologies Used](#technologies-used)
  - [Prerequisites](#prerequisites)
  - [Installation and Setup Instructions](#installation-and-setup-instructions)
  - [Usage Instructions](#usage-instructions)
  - [Modifying / Extending the Application](#modifying--extending-the-application)
    - [How to Extend:](#how-to-extend)
    - [Future Enhancements](#future-enhancements)
  - [Developed By](#developed-by)




## Features

- Users can post messages.
- Users can vote on polls and create new polls.
- Users can view & add 'family dreams' i.e dreams we all share for the future, like visit Hawaii!
- Admins can delete messages & dreams. 
- Admins can post poll results for users to view. 
- AWS SNS notifications are sent for new dreams and poll results.

## Architecture Overview

The application consists of two AWS EC2 instances: one hosting the user interface and another hosting the admin interface. Both instances interact with an AWS RDS database for data storage and use AWS Simple Notification Service (SNS) for notifications. 

## Technologies Used 
- PHP, JavaScript, HTML, CSS.
- AWS SDK for PHP, Boostrap for styling.
- Cloud Services: 
  - AWS EC2: Hosting the user and admin servers.
  - AWS RDS (MySQL): Hosting relational database storage.
  - AWS SNS: Sending notifications.
- Git for version control. 

## Prerequisites

- AWS account with proper permissions.
- Git is installed for cloning the repository
  - You can check if Git is installed by running: 
  
            git --version

- SSH client is helpful for editing code, e.g. Remote-SSH in VSCode.

## Installation and Setup Instructions 


**1. For both the User and Admin VM's:**
   - Create EC2 Instances: 
      - AMI: Use AMazon Linux 2023. 
      - Instance Type: t2.micro (free tier eligigle).
      - Security Groups: Open Ports 22 (SSH) and 80 (HTTP).
  
**2. Connect to the Instances using SSH:**

        ssh -i your-key.pem ec2-user@your-ec2-instance-public-dns

**3. Install Dependencies:** 

        sudo yum update -y
        sudo amazon-linux-extras install php7.4
        sudo yum install -y httpd git

**4. Start and Enable Apache:**

        sudo systemctl start httpd
        sudo systemctl enable httpd

**5. Clone the repository on each VM, and copy the '/user' or '/admin' files to '/var/www/html' in their respective VM:**

        cd /home/ec2-user
        sudo git clone https://github.com/Kiwirish/my-family-app.git
        sudo rm -rf /var/www/html/*
        sudo cp -r user/* /var/www/html/

    (sudo will usually need to be invoked)
    (For the Admin VM, 'user' becomes 'admin')

**6. Configure PHP and AWS SDK:**

        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        php composer-setup.php
        php -r "unlink('composer-setup.php');"
        sudo mv composer.phar /usr/local/bin/composer
        cd /var/www/html
        composer require aws/aws-sdk-php
    
  - (Do above IF '/vendor' file in repository doesn't already do so)

**7. Setting up the RDS:****
    - Create RDS instance.
    - Settings: MySQL, db.t2.micro, DB instance identifier: family-app-db, Master Username: admin, Master Password: mypassword.
    - Allow inbound MySQL/Aurora (3306) traffic. 


**8. Configure VPC Security Groups**

    Both EC2 instances need to be able to communicate with the RDS, so create and the following security groups and add them to the appropriate AWS services: 

        - Internet Access
        - Database Access
        - Database
        - SSH Access - Internet 
        - Web Access - Internet 

  - Otherwise, ensure that the security group inbound rules attached to each service is successfully communicating with each other.

**9. Initialise Database**

- Connect to the RDS using a MySQL client from one of the two EC2 instances: 
        mysql -h rds-endpoint -u admin -p

- Create the Family database and tables using the provided SQL script.

**10. Create SNS topics:**

    - FamilyDreamNotifications
    - PollResultNotifications

    Add subscriptions to the topics, then update the code with the SNS topic ARNs. 

**11. Environment Variables:**
    
- in each EC2 instance, set your environment varibles with: 
        export AWS_ACCESS_KEY_ID=
        export AWS_SECRET_ACCESS_KEY=
        export AWS_SESSION_TOKEN=
        export AWS_REGION=

**12. Optional: Create elastic IP addresses for each VM so they're always at the same location on each run.**


## Usage Instructions

**Accessing the Application:**

- User Interface: 
        http://user-server-public-dns/index.php

- Admin Interface: 
        http://admin-server-public-dns/index.php

(Or at elastic IP's attached) 



## Modifying / Extending the Application

Developers can extend the application by adding new PHP scripts for user and admin actions. Each new feature added should be tied to an EC2 instances respective responsibility:

 - User VM Server: General family user capabilities.
 - Admin VM Server: Administrative functionality - data editing and removal. 
  
### How to Extend:

 - Database Modifications: Any schema changes should be made from one of the EC2 instances as they have permissions to the database through their security group.

 - Web Interface Changes: You can modify or add PHP and CSS files in the *user*  and *admin* directories for changes to the UI or business logic.

### Future Enhancements
The project is designed with future extensibility in mind. Potential new features for my development personally include:

 - Shared Calendar: A calander system for seeing and adding upcoming family events.
 - Basic web minigame to get competitive!
  

---


## Developed By

**Blake Leahy**


- [GitHub](https://github.com/Kiwirish)
- [Portfolio](https://blakeleahy.tech)