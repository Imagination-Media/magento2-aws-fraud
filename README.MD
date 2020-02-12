
<p align="center">
<img src="https://cdn4.iconfinder.com/data/icons/vector-brand-logos/40/AWS-128.png" alt="Amazon Fraud" />
</p>
<p align="center">Amazon Fraud for Magento 2</p>

# Amazon Fraud for Magento 2

### Magento Innovations Lab Partners With Amazon Web Services (AWS)
[Magento Innovation Labs](https://magento.com/innovations-lab) it's a program that allows the community to show creative and innovative solutions for e-commerce, using the Magento platform. Every year a new round is available for the Magento community to create and show how is possible to innovate the e-commerce industry using the flexibility that only Magento has.
This integration was developed thinking about the partnership between Magento and Amazon, as one of the recommended solutions that can be integrated with Magento. For more info please check [https://magento.com/blog/magento-news/magento-innovations-lab-teams-amazon-web-services-aws](https://magento.com/blog/magento-news/magento-innovations-lab-teams-amazon-web-services-aws)

## What is AWS Fraud
[Amazon Fraud]([https://aws.amazon.com/fraud-detector/?nc1=h_ls](https://aws.amazon.com/fraud-detector/?nc1=h_ls)) is a fully managed service that makes it easy to identify potentially fraudulent online activities such as online payment fraud and the creation of fake accounts.

Globally each year, tens of billions of dollars are lost to online fraud. Companies conducting business online are especially prone to attacks from bad actors who often exploit different tactics such as creating fake accounts and making payments with stolen credit cards. Companies typically use fraud detection applications to identify fraudsters and stop them before they cause costly business disruptions. However, these applications often rely on business rules that don’t keep up with the changing behaviors of fraudsters. More recent fraud detection applications have tried to use machine learning. But, they frequently use a one-size-fits-all approach based on general data sets and fraud behaviors that aren't specific to your business, which limits their accuracy.

## Installation
Install using composer:
```ssh
compose require imaginationmedia/aws-fraud-magento2
```
 You will also need to create a new AWS Fraud project in your AWS console, you start [using this link.]([https://console.aws.amazon.com/frauddetector/home?#](https://console.aws.amazon.com/frauddetector/home?#))

## How it works with Magento
AWS Fraud will validate when a customer tries to create a new account, when he/she tries to login and also when customers are trying to checkout as guests.

This Magento integration will get the customer email address and ip, and using this information we will validate on AWS Fraud what's the probability of this customer be a fraud. In case it's higher than the limit we set on Magento admin, it will automatically deny the login/account creation and also decline the guest checkout.

Also on admin you can access a customer account and there is a tab where you can see the probability a customer is a fraud account. If a customer is set as fraud he/she won't be able to login and checkout anymore.

## How to start
You basically need to go through five steps:

-   ### Step 1
    
    Upload your historical fraud datasets to Amazon S3.
-   ### Step 2
    
    Select from pre-built fraud detection model templates.
-   ### Step 3
    
    The model template uses your historical data as input to build a custom model. The model template inspects and enriches data, performs feature engineering, selects algorithms, trains and tunes your model, and hosts the model.
-   ### Step 4
    
    Create rules to either accept, review, or collect more information based on model predictions.
-   ### Step 5
    
    Install the Magento integration and setup all the configuration.

The historical fraud dataset is a csv file with email addresses, ips and the fraud status (fraud, not_fraud etc). Using this csv file Amazon Fraud will study and learn how to identify fraud customers, and in short time it will be able to determine the probability a customer is a fraud.

### Basic configuration
On Magento admin you will need to first setup the basic configuration, used to create the connection to AWS Personalize.

![Basic configuration](https://i.ibb.co/F8xqrMp/fraud.png)

**Enable** is used to enable/disable the fraud validation.
**Profile** is the AWS profile that you have in your environment/server that's going to be used to connect to AWS. Check this [guide for more info.](https://docs.aws.amazon.com/cli/latest/userguide/cli-configure-profiles.html)
**Version** is the web service version that you are going to use. Use **latest** as the value, to use always the latest version.
**Region** is the region where you created your AWS Personalize instance.
**Detector ID** is the ID of the fraud detector that you created in your AWS account.
**Event ID** is the event that you enabled in your fraud detector.
**Fraud Rate** is the minimum rate that we will consider a customer as a fraud account. If the probability reaches this value we will automatically block the user to proceed. In general if a customer reaches a probability of 80% we can say it's a fraud customer.

## Demos:

### Checking fraud probability on Magento admin and blocking a fraud customer to login:
https://www.loom.com/share/c6d9f951367643348c75cd31bf33b207

### Preventing a fake user to create a new account
https://www.loom.com/share/a6a7536001844511ab06f3fe9eb919d2

### Declining a fake user to checkout as a guest
https://www.loom.com/share/9c895bce8f50464cbf529271d7c2eddf