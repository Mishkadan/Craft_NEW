/*
* @package 		progressivewebappmaker - Progressive Web App Maker
* @version		1.0.5
* @created		Jan 2020
* @author		ExtensionCoder.com
* @email		developer@extensioncoder.com
* @website		https://www.extensioncoder.com
* @support		https://www.extensioncoder.com/support.html
* @copyright	Copyright (C) 2019-2020 ExtensionCoder. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

// 25.07.22  add push event
self.addEventListener("push",function(i){var t=i.data.json(),n=t.notification,o=n.title,a=n.body,c=n.icon,l=n.title,t={url:{clickurl:n.click_action}};i.waitUntil(self.registration.showNotification(o,{body:a,icon:c,tag:l,data:t}))}),self.addEventListener("notificationclick",function(i){i.notification.close();var t=i.notification.data.url,n=t.clickurl;i.waitUntil(clients.openWindow(n))});


var url="",
    pwamakerPwaVersion="v1.0",
    staticCacheName="pwa_maker_pwa-static-"+pwamakerPwaVersion,
    dynamicCacheName="pwa_maker_pwa-dynamic-"+pwamakerPwaVersion;

self.addEventListener("install",function(e){
    e.waitUntil(self.skipWaiting())
}),
    self.addEventListener("activate",function(e){
        return e.waitUntil(caches.keys().then(function(e){
            return Promise.all(e.map(function(e){
                if(e!==staticCacheName && e!==dynamicCacheName) return caches.delete(e)
            }))
        })),
            self.clients.claim()}),
    self.addEventListener("fetch",function(e){
        if("POST" != e.request.method){
            var t=new URL(e.request.url);
            "/pwa_maker_service_worker.js"==t.pathname||t.pathname.indexOf(".mp3")>-1||e.respondWith(caches.match(e.request).then(function(t){
                    return fetch(e.request).then(function(t){
                        return caches.open(dynamicCacheName).then(function(a){
                            return 0===e.request.url.indexOf("http")&&a.put(e.request,t.clone()),t
                        })
                    }).catch(function(e){
                        return t||function(){}
                    })
                }).catch(function(){return caches.match("Error")
                })
            )
        }
    });