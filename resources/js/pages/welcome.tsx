import { Head, useForm, router } from '@inertiajs/react';
import { useMemo, useState, type FormEvent } from 'react';
import { route } from 'ziggy-js';

interface User {
    id: number;
    username: string;
    nickname: string;
}

type SortKey = 'id' | 'username' | 'nickname';
type SortDirection = 'asc' | 'desc';

export default function Welcome({ users }: { users: User[] }) {
    const { setData, post, processing } = useForm({
        nickname: '',
        username: '',
        password: '',
        _method: 'post',
    });
    const [sortConfig, setSortConfig] = useState<{
        key: SortKey;
        direction: SortDirection;
    }>({
        key: 'id',
        direction: 'asc',
    });

    const handleSort = (key: SortKey) => {
        setSortConfig((prev) => ({
            key,
            direction:
                prev.key === key && prev.direction === 'asc' ? 'desc' : 'asc',
        }));
    };

    const sortedUsers = useMemo(() => {
        const items = [...users];
        items.sort((a, b) => {
            if (
                sortConfig.key === 'username' ||
                sortConfig.key === 'nickname'
            ) {
                return sortConfig.direction === 'asc'
                    ? a[sortConfig.key].localeCompare(b[sortConfig.key])
                    : b[sortConfig.key].localeCompare(a[sortConfig.key]);
            }

            return sortConfig.direction === 'asc' ? a.id - b.id : b.id - a.id;
        });
        return items;
    }, [users, sortConfig]);

    const getSortIndicator = (key: SortKey) => {
        if (sortConfig.key !== key) return ' ↕';
        return sortConfig.direction === 'asc' ? ' ↑' : ' ↓';
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(route('users.store'), { forceFormData: true });
    };

    const remove = (id: number) => {
        router.delete(route('users.destroy', id));
    };

    return (
        <>
            <Head title="Welcome" />
            <div className="flex flex-col">
                <table className="mx-auto mt-20 w-1/2 table-auto border-collapse">
                    <thead>
                        <tr>
                            <th className="border-none"></th>
                            <th
                                onClick={() => handleSort('id')}
                                className="cursor-pointer border border-slate-300 p-2 select-none"
                            >
                                ID{getSortIndicator('id')}
                            </th>
                            <th
                                onClick={() => handleSort('username')}
                                className="cursor-pointer border border-slate-300 p-2 select-none"
                            >
                                Username{getSortIndicator('username')}
                            </th>
                            <th
                                onClick={() => handleSort('nickname')}
                                className="cursor-pointer border border-slate-300 p-2 select-none"
                            >
                                Name{getSortIndicator('nickname')}
                            </th>
                            {/* <th onClick={() => handleSort('size')} className="cursor-pointer select-none border border-slate-300 p-2">
                                Size{getSortIndicator('size')}
                            </th> */}
                        </tr>
                    </thead>
                    <tbody>
                        {sortedUsers.map((user) => (
                            <tr key={user.id}>
                                <td className="flex h-28 flex-row-reverse border-none">
                                    <button
                                        type="button"
                                        className="my-10 mr-4 cursor-pointer rounded-md bg-red-500 px-5 py-1"
                                        onClick={() => remove(user.id)}
                                    >
                                        -
                                    </button>
                                </td>
                                <td className="border border-slate-300 text-center">
                                    {user.id}
                                </td>
                                <td className="border border-slate-300 text-center">
                                    {user.username}
                                </td>
                                <td className="border border-slate-300 text-center">
                                    {user.nickname}
                                </td>
                                {/* <td className="border border-slate-300 text-center">{game.password}</td> */}
                            </tr>
                        ))}
                    </tbody>
                </table>

                <form onSubmit={submit}>
                    <div className="mx-auto mt-32 ml-100 flex w-full flex-row gap-10">
                        <div className="flex flex-col gap-5">
                            <input
                                type="text"
                                id="username"
                                className="rounded-sm bg-white text-black"
                                placeholder="Username"
                                onInput={(e) =>
                                    setData('username', e.currentTarget.value)
                                }
                                required
                            />
                            <input
                                type="text"
                                id="nickname"
                                className="rounded-sm bg-white text-black"
                                placeholder="Nickname"
                                onInput={(e) =>
                                    setData('nickname', e.currentTarget.value)
                                }
                                required
                            />
                            <input
                                type="password"
                                id="password"
                                className="rounded-sm bg-white text-black"
                                placeholder="Password"
                                onInput={(e) =>
                                    setData('password', e.currentTarget.value)
                                }
                                required
                            />
                        </div>
                        <button
                            type="submit"
                            className="my-10 cursor-pointer rounded-md bg-green-500 px-5 py-1"
                        >
                            {processing ? 'Saving' : '+'}
                        </button>
                    </div>
                </form>
            </div>
        </>
    );
}
