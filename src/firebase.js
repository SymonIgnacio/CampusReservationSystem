import { initializeApp } from "firebase/app";
import { getAuth } from "firebase/auth";

const firebaseConfig = {
  apiKey: "AIzaSyA68bKIyv4QgZjeDaQW7XXVqyZRTlXCBHI",
  authDomain: "crs-dyci.firebaseapp.com",
  projectId: "crs-dyci",
  storageBucket: "crs-dyci.firebasestorage.app",
  messagingSenderId: "913636526521",
  appId: "1:913636526521:web:3467014ea38ac912710e68",
  measurementId: "G-LKT4TR0Z77"
};

const app = initializeApp(firebaseConfig);
export const auth = getAuth(app);
export default app;