from multiprocessing import Process, Queue
import random

'''
输出ping pong合计10次
'''


def sub_task(q, string):
    counter = q.get(True)
    print('输出字符串 %s,%d次', string, counter)
    while counter > 0:
        print(string, end='\n', flush=True)
        q.put(counter)
        counter -= 1


def main():
    q = Queue()
    n1 = random.randint(1, 10)
    n2 = 10 - n1
    rand_list = {
        't': {'num': n1, 'str': 'Ping'},
        'b': {'num': n2, 'str': 'Pong'},
    }
    for i in rand_list:
        q.put(rand_list[i]['num'])
        Process(target=sub_task, args=(q, rand_list[i]['str'],)).start()


if __name__ == '__main__':
    main()
