db = db.getSiblingDB(process.env.MONGO_INITDB_DATABASE || "laravel");

db.createUser({
  user: process.env.MONGO_APP_USERNAME || "laravel",
  pwd: process.env.MONGO_APP_PASSWORD || "secret",
  roles: [
    { role: "readWrite", db: process.env.MONGO_INITDB_DATABASE || "laravel" }
  ]
});