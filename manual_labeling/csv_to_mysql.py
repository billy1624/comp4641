import time
import sys
import pandas as pd
import mysql.connector
from sqlalchemy import types, create_engine

cols = [
    "date",
    "user",
    "is_retweet",
    "is_quote",
    "text",
    "quoted_text",
    "original_tweet",
    "lat",
    "long",
    "hts",
    "mentions",
    "tweet_id",
    "likes",
    "retweets",
    "replies",
    "quote_count",
    "original_tweet_id",
]

# MySQL Connection
MYSQL_USER      = 'root'
MYSQL_PASSWORD  = 'root'
MYSQL_HOST_IP   = '127.0.0.1'
MYSQL_PORT      = '3306'
MYSQL_DATABASE  = 'comp4641'
MYSQL_CHARSET   = 'utf8mb4'

engine = create_engine('mysql+mysqlconnector://'+MYSQL_USER+':'+MYSQL_PASSWORD+'@'+MYSQL_HOST_IP+':'+MYSQL_PORT+'/'+MYSQL_DATABASE+'?charset='+MYSQL_CHARSET, echo=False)

file_name = sys.argv[1]

total_start = time.time()
batch_start = time.time()
batch_size = 10000
rows = 0

for df in pd.read_csv(file_name, chunksize=batch_size, usecols=cols, encoding='utf-8', dtype=str):

    rows += len(df.index)
    # df.to_sql(name='tweets', con=engine, if_exists='append', chunksize=1, index=False)
    # df.to_sql(name='tweets_copy', con=engine, if_exists='append', chunksize=1, index=False)

    batch_end = time.time()
    print("Processed: {:,} | Batch: {:,.3f} sec | Total: {:,.3f} min".format(rows, batch_end-batch_start, (batch_end-total_start)/60));
    batch_start = time.time()
