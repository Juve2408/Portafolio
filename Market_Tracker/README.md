# Steam_Market_Tracker
A lightweight, automated backend tool built with Python and SQLite to track real-time item pricing on the Steam Community Market. It features dynamic trend comparison against historical records and triggers instant Discord webhook notifications upon detecting price fluctuations, optimizing trade and investment decisions.
<img width="600" height="313" alt="Screenshot 2026-11-16 210156" src="https://github.com/user-attachments/assets/9bcc8be3-e0f6-41db-ab4e-7990096d7b44" />

<img width="656" height="195" alt="image" src="https://github.com/user-attachments/assets/9069e5ce-c28f-449b-8faa-de11955d0ac7" />

There are some steps in order to run this project

**1.Discord WebHook**

  To let discord send you messages with the code you need to set up a hook on a server, but you must get permission to set up a webhook. There are the steps to set up the webhook:
  
  1.Go to a channel and select edit channel
  
  <img width="431" height="136" alt="image" src="https://github.com/user-attachments/assets/1f24fca1-9e79-4d27-9fe1-2770e0221e89" />

  2.Go to integrations 

  <img width="327" height="176" alt="image" src="https://github.com/user-attachments/assets/2e09d6eb-7914-4156-8699-879c3a177e3d" />

  3.Create your Webhook and copy webhook url

  <img width="606" height="293" alt="image" src="https://github.com/user-attachments/assets/b76ea98a-81a2-443b-8736-5862b593fdad" />

  4.Paste it on this line

<img width="1435" height="75" alt="image" src="https://github.com/user-attachments/assets/c9ae93bc-21bf-4364-9e8e-8904a1b6e7e8" />

**2.The urls you want to analyse**
In order to get the analysis of your products you need to copy the url by the next way:

You need to go to the market(https://steamcommunity.com/market/) and copy the url like this

<img width="727" height="300" alt="Screenshot 2026-11-16 212420" src="https://github.com/user-attachments/assets/3f1baddf-6ef8-412e-a649-b6dcc30241f9" />

And you ned to get the link like this:
"https://steamcommunity.com/market/listings/730/Dreams%20%26%20Nightmares%20Case".
if you copy the link like this way, it wont work

<img width="556" height="47" alt="image" src="https://github.com/user-attachments/assets/4e4e4ea7-7a05-458f-863f-4f59b474be15" />

And after that past as many as you want in this lines

<img width="988" height="185" alt="image" src="https://github.com/user-attachments/assets/a0c72df4-2901-44bc-8150-12ad8744fdf6" />

**3.Put a task scheduler with the code**

Go to task scheduler on windows and choose create a basic task and put it the name you want. After that Chosse next

<img width="1380" height="716" alt="image" src="https://github.com/user-attachments/assets/9b053d18-36b5-4c2b-8504-6b2f82953eeb" />

In the trigger section chosse whenever you want to run the code in this case daily and put how much days you want to run the code

<img width="802" height="170" alt="image" src="https://github.com/user-attachments/assets/a4c3e86c-aeef-4804-83aa-61e54c67e5c9" />

<img width="737" height="111" alt="image" src="https://github.com/user-attachments/assets/5802f859-0ad2-4101-a31b-2fe4d1260130" />

In action chosse file, in program script put pythonw. In add arguments the name of the file and star in put the complete root of the file 

<img width="836" height="382" alt="image" src="https://github.com/user-attachments/assets/8def3722-6a67-4a09-829c-547683d884ae" />

After that click finish

if you want to repeat the code more than 1 time per day, search you task below in active tasks

<img width="655" height="241" alt="image" src="https://github.com/user-attachments/assets/6da0d5de-1302-4229-88e5-98e64d9e5ad9" />

Now search again youre task in the sop center window and select properties

<img width="407" height="401" alt="Screenshot 2026-11-16 220025" src="https://github.com/user-attachments/assets/23bc6e9e-b84d-4490-a5e0-e65f69087a1a" />

Go to trigger edit it and you can chosse on advanced settings how often you want to run the code

<img width="687" height="587" alt="image" src="https://github.com/user-attachments/assets/b66b8071-7b78-47ba-9d90-202ce802ec69" />


**Tips**
If steam blocks your navigator because of so many times the code go for the data do the next steps:
1.locate this part of the code

<img width="1363" height="97" alt="image" src="https://github.com/user-attachments/assets/fec5d02a-0f4a-4248-97ed-ab3f9ccfd80a" />

2. go to the item you want to analyse and inspect the page
3. Go to network section and press f5
4. go to the first document and selec it
5. locate the request headers and search cookie
6. copy that text and paste it in the code in fron of "Cookie":
7. Go to the 72 line and edit that line like this

<img width="722" height="70" alt="image" src="https://github.com/user-attachments/assets/26ad6a91-a6e4-4f02-b10e-e9bb1a496d6e" />





