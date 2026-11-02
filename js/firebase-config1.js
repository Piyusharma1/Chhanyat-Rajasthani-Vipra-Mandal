// js/firebase-config.js

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js";

import {
  initializeFirestore,
  persistentLocalCache,
  persistentMultipleTabManager
} from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";

import { getAuth } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js";

const firebaseConfig = {
  apiKey: "AIzaSyCz55tp_ItJJvUFFyX-svQ2_l1KOcWrkco",
  authDomain: "application-380507.firebaseapp.com",
  projectId: "application-380507",
  storageBucket: "application-380507.firebasestorage.app",
  messagingSenderId: "332604869705",
  appId: "1:332604869705:web:526b81c786caa18a5ac398",
  measurementId: "G-9WSQVKWF25"
};

const app = initializeApp(firebaseConfig);

const db = initializeFirestore(app, {
  localCache: persistentLocalCache({
    tabManager: persistentMultipleTabManager()
  })
});

const auth = getAuth(app);

const ADMIN_UID = "217Sc2fLMbh8nHQTL1yvPjlOE7B2";

export { app, db, auth, ADMIN_UID };